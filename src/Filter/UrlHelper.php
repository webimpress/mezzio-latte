<?php

namespace Zend\Expressive\Latte\Filter;

class UrlHelper
{
    private $urlHelper;

    public function __construct(\Zend\Expressive\Helper\UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    public function __invoke()
    {
        return $this->urlHelper->generate(... func_get_args());
    }
}
