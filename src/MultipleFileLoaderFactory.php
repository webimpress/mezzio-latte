<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-latte for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-latte/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Latte;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Latte\Exception\InvalidConfigException;
use Zend\Expressive\Latte\Exception\NamespaceUsedException;

class MultipleFileLoaderFactory
{
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
