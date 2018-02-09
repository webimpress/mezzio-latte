<?php

declare(strict_types=1);

namespace Zend\Expressive\Latte\Exception;

use DomainException;

class InvalidLoaderException extends DomainException implements ExceptionInterface
{
}
