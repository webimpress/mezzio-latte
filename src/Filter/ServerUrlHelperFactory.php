<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte\Filter;

use Mezzio\Helper\ServerUrlHelper as MezzioServerUrlHelper;
use Psr\Container\ContainerInterface;

class ServerUrlHelperFactory
{
    public function __invoke(ContainerInterface $container) : ServerUrlHelper
    {
        $serverUrlHelper = $container->get(MezzioServerUrlHelper::class);

        return new ServerUrlHelper($serverUrlHelper);
    }
}
