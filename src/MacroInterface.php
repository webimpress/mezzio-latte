<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte;

use Latte\MacroNode;
use Latte\PhpWriter;

interface MacroInterface
{
    public function begin(MacroNode $node, PhpWriter $writer) : string;

    public function end(MacroNode $node, PhpWriter $writer) : ?string;

    public function attr(MacroNode $node, PhpWriter $writer) : ?string;

    public function flag() : ?int;
}
