<?php

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
