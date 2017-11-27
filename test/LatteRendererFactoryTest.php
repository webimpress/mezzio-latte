<?php

namespace ZendTest\Expressive\Latte;

use Latte\Engine;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Latte\LatteRenderer;
use Zend\Expressive\Latte\LatteRendererFactory;
use Zend\Expressive\Latte\MultiplePathLoaderInterface;

class LatteRendererFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $container;

    /**
     * @var LatteRendererFactory
     */
    private $factory;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->factory = new LatteRendererFactory();
    }

    public function testInvokeCreatesInstance() : void
    {
        $config = [];
        $loader = $this->prophesize(MultiplePathLoaderInterface::class);

        $this->container->get('config')->willReturn($config);
        $this->container->get(MultiplePathLoaderInterface::class)
            ->willReturn($loader)
            ->shouldBeCalledTimes(1);

        $instance = ($this->factory)($this->container->reveal());

        self::assertInstanceOf(LatteRenderer::class, $instance);
        self::assertAttributeInstanceOf(Engine::class, 'template', $instance);
    }

    public function testInvokeCreatesInstanceWithCacheDir() : void
    {
        $config = [
            'latte' => [
                'cache_dir' => 'foo/cache/bar',
            ],
        ];

        $this->container->get('config')->willReturn($config);
        $this->container->get(MultiplePathLoaderInterface::class)->willReturn(
            $this->prophesize(MultiplePathLoaderInterface::class)->reveal()
        );

        $instance = ($this->factory)($this->container->reveal());

        self::assertInstanceOf(LatteRenderer::class, $instance);
        self::assertAttributeInstanceOf(Engine::class, 'template', $instance);
        $engine = new \ReflectionProperty($instance, 'template');
        $engine->setAccessible(true);
        self::assertAttributeSame('foo/cache/bar', 'tempDirectory', $engine->getValue($instance));
    }
