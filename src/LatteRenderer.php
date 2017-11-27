<?php

namespace Zend\Expressive\Latte;

use Latte\Engine;
use Zend\Expressive\Latte\Exception\InvalidLoaderException;
use Zend\Expressive\Template\ArrayParametersTrait;
use Zend\Expressive\Template\DefaultParamsTrait;
use Zend\Expressive\Template\TemplatePath;
use Zend\Expressive\Template\TemplateRendererInterface;

class LatteRenderer implements TemplateRendererInterface
{
    use ArrayParametersTrait;
    use DefaultParamsTrait;

    /**
     * @var Engine
     */
    private $template;

    /**
     * @var MultiplePathLoaderInterface
     */
    private $loader;

    public function __construct(Engine $template)
    {
        $this->template = $template;
        $this->loader = $template->getLoader();

        if (! $this->loader instanceof MultiplePathLoaderInterface) {
            throw new InvalidLoaderException(sprintf(
                'Latte loader must be an instance of %s; "%s" given',
                MultiplePathLoaderInterface::class,
                is_object($this->loader) ? get_class($this->loader) : gettype($this->loader)
            ));
        }
    }

    /**
     * Render a template, optionally with parameters.
     *
     * Implementations MUST support the `namespace::template` naming convention,
     * and allow omitting the filename extension.
     *
     * @param string $name
     * @param array|object $params
     * @return string
     */
    public function render($name, $params = [])
    {
        // Merge parameters based on requested template name
        $params = $this->mergeParams($name, $this->normalizeParams($params));

        return $this->template->renderToString($name, $params);
    }

    /**
     * Add a template path to the engine.
     *
     * Adds a template path, with optional namespace the templates in that path
     * provide.
     *
     * @param string $path
     * @param string $namespace
     */
    public function addPath($path, $namespace = null)
    {
        $this->loader->addPath($path, $namespace);
    }

    /**
     * Retrieve configured paths from the engine.
     *
     * @return TemplatePath[]
     */
    public function getPaths()
    {
        $paths = [];
        foreach ($this->loader->getPaths() as $namespace => $path) {
            $paths[] = new TemplatePath($path, $namespace);
        }

        return $paths;
    }
}
