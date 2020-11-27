<?php

declare(strict_types=1);

namespace WebimpressTest\Mezzio\Latte;

use Generator;
use Mezzio\Template\Exception\ExceptionInterface as TemplateExceptionInterface;
use PHPUnit\Framework\TestCase;
use Webimpress\Mezzio\Latte\Exception\ExceptionInterface;

use function basename;
use function glob;
use function is_a;
use function strrpos;
use function substr;

class ExceptionTest extends TestCase
{
    public function testExceptionInterfaceExtendsTemplateExceptionInterface() : void
    {
        self::assertTrue(is_a(ExceptionInterface::class, TemplateExceptionInterface::class, true));
    }

    public function exception() : Generator
    {
        $namespace = substr(ExceptionInterface::class, 0, strrpos(ExceptionInterface::class, '\\') + 1);

        $exceptions = glob(__DIR__ . '/../src/Exception/*.php');
        foreach ($exceptions as $exception) {
            $class = substr(basename($exception), 0, -4);

            yield $class => [$namespace . $class];
        }
    }

    /**
     * @dataProvider exception
     */
    public function testExceptionIsInstanceOfExceptionInterface(string $exception) : void
    {
        self::assertStringContainsString('Exception', $exception);
        self::assertTrue(is_a($exception, ExceptionInterface::class, true));
    }
}
