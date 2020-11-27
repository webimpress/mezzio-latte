<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte\Exception;

use DomainException;

class InvalidLoaderException extends DomainException implements ExceptionInterface
{
}
