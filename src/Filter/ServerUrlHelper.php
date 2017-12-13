<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-latte for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-latte/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Latte\Filter;

class ServerUrlHelper
{
    private $serverUrlHelper;

    public function __construct(\Zend\Expressive\Helper\ServerUrlHelper $serverUrlHelper)
    {
        $this->serverUrlHelper = $serverUrlHelper;
    }

    public function __invoke()
    {
        return $this->serverUrlHelper->generate(... func_get_args());
    }
}
