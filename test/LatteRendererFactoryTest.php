<?php

declare(strict_types=1);

namespace ZendTest\Expressive\Latte;

use Latte\Engine;
use Latte\Runtime\FilterInfo;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use stdClass;
use Zend\Expressive\Latte\Exception\InvalidMacroException;
use Zend\Expressive\Latte\LatteRenderer;
use Zend\Expressive\Latte\LatteRendererFactory;
use Zend\Expressive\Latte\MultipleFileLoader;
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

    public function testCustomFilters() : void
    {
        $invokable = new class()
        {
            public function __invoke($s)
            {
                return 'invokable' . $s;
            }
        };

        $info = new class()
        {
            public function info(FilterInfo $info, $s)
            {
                return 'info' . $s;
            }
        };

        $factory = new class()
        {
            public function __invoke($s)
            {
                return 'factory' . $s;
            }
        };

        $config = [
            'latte' => [
                'filters' => [
                    function ($filterName, $s) {
                        return 'dynamic@' . $filterName . '@' . $s;
                    },
                    'callback' => function ($s) {
                        return 'callback' . $s;
                    },
                    'factory' => 'my-factory-filter',
                    'invokable' => $invokable,
                    'ext1' => [TestAsset\Filter::class, 'ext1'],
                    'ext2' => TestAsset\Filter::class . '::ext2',
                    'info' => [$info, 'info'],
                ],
            ],
        ];

        $content = <<<'EOC'
Origin var: {$var}
Dynamic filter: {$var|notDefined}
Callback filter: {$var|callback}
Factory filter: {$var|factory}
Invokable filter: {$var|invokable}
Ext1 filter (no params): {$var|ext1}
Ext1 filter (with param): {$var|ext1,7}
Ext2 filter (no params): {$var|ext2}
Ext2 filter (with one param): {$var|ext2,3}
Ext2 filter (with two params): {$var|ext2,4,5}
Info filter: {$var|info}
EOC;

        $root = vfsStream::setup(__FUNCTION__);
        vfsStream::newFile('my-template.latte')
            ->at($root)
            ->setContent($content);

        $loader = new MultipleFileLoader('latte');
        $loader->addPath($root->url());

        $this->container->get('config')->willReturn($config);
        $this->container->get(MultiplePathLoaderInterface::class)->willReturn($loader);
        $this->container->has(TestAsset\Filter::class . '::ext2')->willReturn(false);
        $this->container->has('my-factory-filter')->willReturn(true);
        $this->container->get('my-factory-filter')->willReturn($factory);

        /** @var LatteRenderer $renderer */
        $renderer = ($this->factory)($this->container->reveal());

        $result = $renderer->render('my-template', ['var' => 'FooBar']);

        $expected = <<<'EOC'
Origin var: FooBar
Dynamic filter: dynamic@notdefined@FooBar
Callback filter: callbackFooBar
Factory filter: factoryFooBar
Invokable filter: invokableFooBar
Ext1 filter (no params): ext1FooBar
Ext1 filter (with param): ext1FooBar7
Ext2 filter (no params): ext2FooBar|1|2|
Ext2 filter (with one param): ext2FooBar|3|2|
Ext2 filter (with two params): ext2FooBar|4|5|
Info filter: infoFooBar
EOC;

        self::assertSame($expected, $result);
    }

    public function testCustomMacros() : void
    {
        $config = [
            'latte' => [
                'macros' => [
                    'my1' => ['echo ">>"', 'echo "<<"'],
                    'my2' => TestAsset\Macro::class,
                    'my3' => new TestAsset\Macro(),
                ],
            ],
        ];

        $content = <<<'EOC'
Origin var: {$var}
{my1}My Macro 1{/my1}
<p n:my2="$var">My Macro 2</p>
<p n:tag-my3="$bar">My Macro 3</p>
EOC;

        $root = vfsStream::setup(__FUNCTION__);
        vfsStream::newFile('my-template.latte')
            ->at($root)
            ->setContent($content);

        $loader = new MultipleFileLoader('latte');
        $loader->addPath($root->url());

        $this->container->get('config')->willReturn($config);
        $this->container->get(MultiplePathLoaderInterface::class)->willReturn($loader);
        $this->container->has(TestAsset\Macro::class)->willReturn(true);
        $this->container->get(TestAsset\Macro::class)->willReturn(new TestAsset\Macro());

        /** @var LatteRenderer $renderer */
        $renderer = ($this->factory)($this->container->reveal());

        $result = $renderer->render('my-template', ['var' => 'FooBar']);

        $expected = <<<'EOC'
Origin var: FooBar
>>My Macro 1<<
<pattr>My Macro 2</p>
begin<p>endMy Macro 3begin</p>end
EOC;

        self::assertSame($expected, $result);
    }

    public function testInvalidMacroClass() : void
    {
        $config = [
            'latte' => [
                'macros' => [
                    'macro' => 'macro',
                ],
            ],
        ];

        $this->container->get('config')->willReturn($config);
        $this->container->get(MultiplePathLoaderInterface::class)->willReturn(
            $this->prophesize(MultiplePathLoaderInterface::class)
        );
        $this->container->has('macro')->willReturn(true);
        $this->container->get('macro')->willReturn(new stdClass());

        $this->expectException(InvalidMacroException::class);
        $this->expectException(ContainerExceptionInterface::class);
        ($this->factory)($this->container->reveal());
    }
}
