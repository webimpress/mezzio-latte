<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte;

use Latte\Engine;
use Mezzio\Template\ArrayParametersTrait;
use Mezzio\Template\DefaultParamsTrait;
use Mezzio\Template\TemplatePath;
use Mezzio\Template\TemplateRendererInterface;
use Webimpress\Mezzio\Latte\Exception\InvalidLoaderException;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class LatteRenderer implements TemplateRendererInterface
{
    use ArrayParametersTrait;
    use DefaultParamsTrait;

    /** @var Engine */
    private $template;

    /** @var MultiplePathLoaderInterface */
    private $loader;

    /**
     * @throws InvalidLoaderException
     */
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
    public function addPath(string $path, ?string $namespace = null) : void
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
