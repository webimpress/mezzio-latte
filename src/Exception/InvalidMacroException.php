<?php

namespace Zend\Expressive\Latte\Exception;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class InvalidMacroException extends InvalidArgumentException implements 
    ContainerExceptionInterface,
    ExceptionInterface
{
}
