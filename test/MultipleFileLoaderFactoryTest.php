<?php

declare(strict_types=1);

namespace WebimpressTest\Mezzio\Latte;

use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionProperty;
use Webimpress\Mezzio\Latte\Exception\InvalidConfigException;
use Webimpress\Mezzio\Latte\MultipleFileLoader;
use Webimpress\Mezzio\Latte\MultipleFileLoaderFactory;

use function count;

class MultipleFileLoaderFactoryTest extends TestCase
{
    /** @var ContainerInterface|MockObject */
    private $container;

    /** @var MultipleFileLoaderFactory */
    private $factory;

    protected function setUp() : void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = new MultipleFileLoaderFactory();
    }

    public function invalidConfig() : Generator
    {
        yield 'templates-no-array' => [['templates' => 'invalid']];
        yield 'paths-duplicated' => [['templates' => ['paths' => ['namespace' => ['a', 'b']]]]];
        yield 'invalid-extension' => [['templates' => ['extension' => []]]];
    }

    /**
     * @dataProvider invalidConfig
     */
    public function testInvokeWithInvalidConfigurationThrowsInvalidConfigException(array $config) : void
    {
        $this->container->method('get')->with('config')->willReturn($config);

        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container);
    }

    public function testInvokeCreatesInstanceWithoutFileExtension() : void
    {
        $config = [
            'templates' => [
                'extension' => 'foo.bar',
                'paths' => [
                    'baz' => 'my/path/1',
                    'var' => 'my/path/2',
                ],
            ],
        ];

        $this->container->method('get')->with('config')->willReturn($config);

        $instance = ($this->factory)($this->container);

        self::assertInstanceOf(MultipleFileLoader::class, $instance);

        $fileExtension = new ReflectionProperty($instance, 'fileExtension');
        $fileExtension->setAccessible(true);

        self::assertSame('foo.bar', $fileExtension->getValue($instance));

        $paths = new ReflectionProperty($instance, 'paths');
        $paths->setAccessible(true);

        self::assertSame($config['templates']['paths'], $paths->getValue($instance));

        $loaders = new ReflectionProperty($instance, 'loaders');
        $loaders->setAccessible(true);

        self::assertCount(count($config['templates']['paths']), $loaders->getValue($instance));
    }

    public function testInvokeCreatesInstanceWithFileExtension() : void
    {
        $config = [
            'templates' => [
                'paths' => [
                    'foo' => 'bar/baz',
                ],
            ],
        ];

        $this->container->method('get')->with('config')->willReturn($config);

        $instance = ($this->factory)($this->container);

        self::assertInstanceOf(MultipleFileLoader::class, $instance);

        $fileExtension = new ReflectionProperty($instance, 'fileExtension');
        $fileExtension->setAccessible(true);

        self::assertSame('latte', $fileExtension->getValue($instance));

        $paths = new ReflectionProperty($instance, 'paths');
        $paths->setAccessible(true);

        self::assertSame($config['templates']['paths'], $paths->getValue($instance));

        $loaders = new ReflectionProperty($instance, 'loaders');
        $loaders->setAccessible(true);

        self::assertCount(count($config['templates']['paths']), $loaders->getValue($instance));
    }
}
