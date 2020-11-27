<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte\Exception;

use DomainException;
use Psr\Container\ContainerExceptionInterface;

class InvalidConfigException extends DomainException implements
    ContainerExceptionInterface,
    ExceptionInterface
{
}
