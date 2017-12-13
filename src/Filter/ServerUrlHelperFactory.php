<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-latte for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-latte/blob/master/LICENSE.md New BSD License
 */

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
