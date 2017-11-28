<?php

namespace Zend\Expressive\Latte\Filter;

use Psr\Container\ContainerInterface;

class UrlHelperFactory
{
    public function __invoke(ContainerInterface $container) : UrlHelper
    {
        $urlHelper = $container->get(\Zend\Expressive\Helper\UrlHelper::class);

        return new UrlHelper($urlHelper);
    }
}
