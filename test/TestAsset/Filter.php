<?php

namespace ZendTest\Expressive\Latte\TestAsset;

class Filter
{
    public static function ext1($s, $p = null)
    {
        return 'ext1' . $s . $p;
    }   
    
    public static function ext2($s, $p = 1, $r = 2)
    {
        return 'ext2' . $s . '|' . $p . '|' . $r . '|';
    }
}
