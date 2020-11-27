<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte;

use Psr\Container\ContainerInterface;
use Webimpress\Mezzio\Latte\Exception\InvalidConfigException;
use Webimpress\Mezzio\Latte\Exception\NamespaceUsedException;

use function is_array;
use function is_numeric;
use function is_string;

class MultipleFileLoaderFactory
{
    /**
     * @throws InvalidConfigException
     */
    public function __invoke(ContainerInterface $container) : MultipleFileLoader
    {
        $config = $container->get('config')['templates'] ?? [];

        if (! is_array($config)) {
            throw new InvalidConfigException('Invalid templates configuration');
        }

        if (isset($config['extension'])) {
            if (! is_string($config['extension'])) {
                throw new InvalidConfigException('Invalid file extension, must be a string');
            }

            $loader = new MultipleFileLoader($config['extension']);
        } else {
            $loader = new MultipleFileLoader();
        }

        try {
            // Add template paths
            $allPaths = isset($config['paths']) && is_array($config['paths'])
                ? $config['paths']
                : [];
            foreach ($allPaths as $namespace => $paths) {
                $namespace = is_numeric($namespace) ? null : $namespace;
                foreach ((array) $paths as $path) {
                    $loader->addPath($path, $namespace);
                }
            }
        } catch (NamespaceUsedException $e) {
            throw new InvalidConfigException(
                'Invalid template paths configuration',
                $e->getCode(),
                $e
            );
        }

        return $loader;
    }
}
