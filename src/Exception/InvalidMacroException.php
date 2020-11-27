<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte\Exception;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class InvalidMacroException extends InvalidArgumentException implements
    ContainerExceptionInterface,
    ExceptionInterface
{
}
