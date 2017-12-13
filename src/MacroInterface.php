<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-latte for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-latte/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

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
