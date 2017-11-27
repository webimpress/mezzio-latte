<?php

namespace Zend\Expressive\Latte\Exception;

use DomainException;
use Psr\Container\ContainerExceptionInterface;

class InvalidConfigException extends DomainException implements
    ContainerExceptionInterface,
    ExceptionInterface
{
}
