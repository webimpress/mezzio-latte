<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-latte for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-latte/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

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
     * @param array|object $params
     */
    public function render(string $name, $params = []) : string
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
     */
    public function addPath(string $path, string $namespace = null) : void
    {
        $this->loader->addPath($path, $namespace);
    }

    /**
     * Retrieve configured paths from the engine.
     *
     * @return TemplatePath[]
     */
    public function getPaths() : array
    {
        $paths = [];
        foreach ($this->loader->getPaths() as $namespace => $path) {
            $paths[] = new TemplatePath($path, $namespace);
        }

        return $paths;
    }
}
