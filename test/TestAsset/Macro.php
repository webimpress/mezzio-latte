<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-latte for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-latte/blob/master/LICENSE.md New BSD License
 */

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
