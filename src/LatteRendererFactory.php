<?php

namespace Zend\Expressive\Latte;

use Latte\Engine as LatteEngine;
use Psr\Container\ContainerInterface;

class LatteRendererFactory
{
    public function __invoke(ContainerInterface $container) : LatteRenderer
    {
        $config = $container->get('config')['latte'] ?? [];

        $engine = new LatteEngine();
        if (isset($config['cache_dir'])) {
            $engine->setTempDirectory($config['cache_dir']);
        }

        $loader = $container->get(MultiplePathLoaderInterface::class);
        $engine->setLoader($loader);

        return new LatteRenderer($engine);
    }
}
