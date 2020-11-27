<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte\Filter;

use Mezzio\Helper\ServerUrlHelper as MezzioServerUrlHelper;

use function func_get_args;

class ServerUrlHelper
{
    /** @var MezzioServerUrlHelper */
    private $serverUrlHelper;

    public function __construct(MezzioServerUrlHelper $serverUrlHelper)
    {
        $this->serverUrlHelper = $serverUrlHelper;
    }

    public function __invoke() : string
    {
        return $this->serverUrlHelper->generate(...func_get_args());
    }
}
