<?php

declare(strict_types=1);

namespace ZendTest\Expressive\Latte;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Latte\Exception\InvalidConfigException;
use Zend\Expressive\Latte\MultipleFileLoader;
use Zend\Expressive\Latte\MultipleFileLoaderFactory;

class MultipleFileLoaderFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $container;

    /**
     * @var MultipleFileLoaderFactory
     */
    private $factory;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new MultipleFileLoaderFactory();
    }

    public function invalidConfig()
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
        $this->container->get('config')->willReturn($config);

        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
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

        $this->container->get('config')->willReturn($config);

        $instance = ($this->factory)($this->container->reveal());

        self::assertInstanceOf(MultipleFileLoader::class, $instance);
        self::assertAttributeSame('foo.bar', 'fileExtension', $instance);
        self::assertAttributeSame($config['templates']['paths'], 'paths', $instance);
        self::assertAttributeCount(count($config['templates']['paths']), 'loaders', $instance);
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

        $this->container->get('config')->willReturn($config);

        $instance = ($this->factory)($this->container->reveal());

        self::assertInstanceOf(MultipleFileLoader::class, $instance);
        self::assertAttributeSame('latte', 'fileExtension', $instance);
        self::assertAttributeSame($config['templates']['paths'], 'paths', $instance);
        self::assertAttributeCount(count($config['templates']['paths']), 'loaders', $instance);
    }
}
