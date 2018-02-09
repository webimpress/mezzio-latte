<?php

declare(strict_types=1);

namespace Zend\Expressive\Latte\Filter;

use Psr\Container\ContainerInterface;

class ServerUrlHelperFactory
{
    public function __invoke(ContainerInterface $container) : ServerUrlHelper
    {
        $serverUrlHelper = $container->get(\Zend\Expressive\Helper\ServerUrlHelper::class);

        return new ServerUrlHelper($serverUrlHelper);
    }
}
