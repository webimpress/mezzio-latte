<?php

namespace Zend\Expressive\Latte;

use Latte\MacroNode;
use Latte\PhpWriter;

interface MacroInterface
{
    public function begin(MacroNode $node, PhpWriter $writer) : string;

    public function end(MacroNode $node, PhpWriter $writer) : ?string;

    public function attr(MacroNode $node, PhpWriter $writer) : ?string;

    public function flag() : ?int;
}
