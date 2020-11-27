<?php

declare(strict_types=1);

namespace WebimpressTest\Mezzio\Latte\TestAsset;

class Filter
{
    public static function ext1(string $s, ?string $p = null) : string
    {
        return 'ext1' . $s . $p;
    }

    public static function ext2(string $s, int $p = 1, int $r = 2) : string
    {
        return 'ext2' . $s . '|' . $p . '|' . $r . '|';
    }
}
