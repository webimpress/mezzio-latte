<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte\Filter;

use Mezzio\Helper\UrlHelper as MezzioUrlHelper;

use function func_get_args;

class UrlHelper
{
    /** @var MezzioUrlHelper */
    private $urlHelper;

    public function __construct(MezzioUrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    public function __invoke() : string
    {
        return $this->urlHelper->generate(...func_get_args());
    }
}
