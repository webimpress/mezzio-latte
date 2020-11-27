<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte\Filter;

use Mezzio\Helper\UrlHelper as MezzioUrlHelper;
use Psr\Container\ContainerInterface;

class UrlHelperFactory
{
    public function __invoke(ContainerInterface $container) : UrlHelper
    {
        $urlHelper = $container->get(MezzioUrlHelper::class);

        return new UrlHelper($urlHelper);
    }
}
