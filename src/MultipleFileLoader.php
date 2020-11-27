<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte;

use Latte\Loaders\FileLoader;
use Webimpress\Mezzio\Latte\Exception\NamespaceUsedException;
use Webimpress\Mezzio\Latte\Exception\UnknownNamespaceException;

use function explode;
use function sprintf;
use function strpos;

class MultipleFileLoader implements MultiplePathLoaderInterface
{
    /** @var string */
    protected $fileExtension;

    /** @var FileLoader[] */
    protected $loaders = [];

    /** @var string[] */
    protected $paths = [];

    public function __construct(string $fileExtension = 'latte')
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * @throws NamespaceUsedException
     */
    public function addPath(string $path, ?string $namespace = null) : void
    {
        if (isset($this->loaders[$namespace])) {
            throw new NamespaceUsedException(sprintf('Namespace %s is already being used', $namespace));
        }

        $this->loaders[$namespace] = new FileLoader($path);
        $this->paths[$namespace] = $path;
    }

    public function getPaths() : array
    {
        return $this->paths;
    }

    /**
     * @return array [string $namespace, string $file]
     * @throws UnknownNamespaceException
     */
    private function getName(string $name) : array
    {
        if (strpos($name, '::')) {
            [$namespace, $file] = explode('::', $name, 2);
        } else {
            $namespace = null;
            $file = $name;
        }

        if (! isset($this->loaders[$namespace])) {
            throw new UnknownNamespaceException(sprintf('Namespace %s is not defined', $namespace));
        }

        return [$namespace, $file . '.' . $this->fileExtension];
    }

    /**
     * Returns template source code.
     *
     * @param string $name
     */
    public function getContent($name) : string
    {
        [$namespace, $file] = $this->getName($name);

        return $this->loaders[$namespace]->getContent($file);
    }

    /**
     * Checks whether template is expired.
     *
     * @param string $name
     * @param int $time
     */
    public function isExpired($name, $time) : bool
    {
        [$namespace, $file] = $this->getName($name);

        return $this->loaders[$namespace]->isExpired($file, $time);
    }

    /**
     * Returns referred template name.
     *
     * @param string $name
     * @param string $referringName
     */
    public function getReferredName($name, $referringName) : string
    {
        return $name;
    }

    /**
     * Returns unique identifier for caching.
     *
     * @param string $name
     */
    public function getUniqueId($name) : string
    {
        [$namespace, $file] = $this->getName($name);

        return $this->loaders[$namespace]->getUniqueId($file);
    }
}
