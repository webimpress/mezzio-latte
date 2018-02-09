<?php

declare(strict_types=1);

namespace ZendTest\Expressive\Latte\TestAsset;

use Latte\MacroNode;
use Latte\PhpWriter;
use Zend\Expressive\Latte\MacroInterface;

class Macro implements MacroInterface
{
    public function begin(MacroNode $node, PhpWriter $writer) : string
    {
        return $writer->write('echo "begin";');
    }

    public function end(MacroNode $node, PhpWriter $writer) : ?string
    {
        return $writer->write('echo "end";');
    }

    public function attr(MacroNode $node, PhpWriter $writer) : ?string
    {
        return $writer->write('echo "attr";');
    }

    public function flag() : ?int
    {
        return null;
    }
}
