<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte\Macro;

use Latte\IMacro;
use Latte\MacroNode;
use Latte\PhpWriter;
use Webimpress\Mezzio\Latte\MacroInterface;

class Path implements MacroInterface
{
    public function begin(MacroNode $node, PhpWriter $writer) : string
    {
        return $writer->write('echo %modify(call_user_func($this->filters->urlHelper, %node.args))');
    }

    public function end(MacroNode $node, PhpWriter $writer) : ?string
    {
        return null;
    }

    public function attr(MacroNode $node, PhpWriter $writer) : ?string
    {
        return null;
    }

    public function flag() : ?int
    {
        return IMacro::AUTO_CLOSE;
    }
}
