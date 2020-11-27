<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte;

use Latte\ILoader;

interface MultiplePathLoaderInterface extends ILoader
{
    public function addPath(string $path, ?string $namespace = null) : void;

    public function getPaths() : array;
}
