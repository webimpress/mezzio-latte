<?php

namespace ZendTest\Expressive\Latte;

use Generator;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Latte\Exception\ExceptionInterface;
use Zend\Expressive\Template\Exception\ExceptionInterface as TemplateExceptionInterface;

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
        self::assertContains('Exception', $exception);
        self::assertTrue(is_a($exception, ExceptionInterface::class, true));
    }
}
