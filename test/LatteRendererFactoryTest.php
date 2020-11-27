<?php

declare(strict_types=1);

namespace WebimpressTest\Mezzio\Latte;

use Latte\Engine;
use Latte\Runtime\FilterInfo;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionProperty;
use stdClass;
use Webimpress\Mezzio\Latte\Exception\InvalidMacroException;
use Webimpress\Mezzio\Latte\LatteRenderer;
use Webimpress\Mezzio\Latte\LatteRendererFactory;
use Webimpress\Mezzio\Latte\MultipleFileLoader;
use Webimpress\Mezzio\Latte\MultiplePathLoaderInterface;

class LatteRendererFactoryTest extends TestCase
{
    /** @var ContainerInterface|MockObject */
    private $container;

    /** @var LatteRendererFactory */
    private $factory;

    protected function setUp() : void
    {
        $this->container = $this->createMock(ContainerInterface::class);

        $this->factory = new LatteRendererFactory();
    }

    public function testInvokeCreatesInstance() : void
    {
        $config = [];
        $loader = $this->createMock(MultiplePathLoaderInterface::class);

        $this->container->method('get')->willReturnMap([
            ['config', $config],
            [MultiplePathLoaderInterface::class, $loader],
        ]);

        $instance = ($this->factory)($this->container);

        self::assertInstanceOf(LatteRenderer::class, $instance);

        $engine = new ReflectionProperty($instance, 'template');
        $engine->setAccessible(true);

        self::assertInstanceOf(Engine::class, $engine->getValue($instance));
    }

    public function testInvokeCreatesInstanceWithCacheDir() : void
    {
        $config = [
            'latte' => [
                'cache_dir' => 'foo/cache/bar',
            ],
        ];

        $this->container->method('get')->willReturnMap([
            ['config', $config],
            [MultiplePathLoaderInterface::class, $this->createMock(MultiplePathLoaderInterface::class)],
        ]);

        $instance = ($this->factory)($this->container);

        self::assertInstanceOf(LatteRenderer::class, $instance);

        $engine = new ReflectionProperty($instance, 'template');
        $engine->setAccessible(true);

        self::assertInstanceOf(Engine::class, $engine->getValue($instance));

        $tempDirectory = new ReflectionProperty($engine->getValue($instance), 'tempDirectory');
        $tempDirectory->setAccessible(true);

        self::assertSame('foo/cache/bar', $tempDirectory->getValue($engine->getValue($instance)));
    }

    public function testCustomFilters() : void
    {
        $invokable = new class() {
            public function __invoke(string $s) : string
            {
                return 'invokable' . $s;
            }
        };

        $info = new class() {
            public function info(FilterInfo $info, string $s) : string
            {
                return 'info' . $s;
            }
        };

        $factory = new class() {
            public function __invoke(string $s) : string
            {
                return 'factory' . $s;
            }
        };

        $config = [
            'latte' => [
                'filters' => [
                    static function (string $filterName, string $s) : string {
                        return 'dynamic@' . $filterName . '@' . $s;
                    },
                    'callback' => static function (string $s) : string {
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

        $this->container->method('get')->willReturnMap([
            ['config', $config],
            [MultiplePathLoaderInterface::class, $loader],
            ['my-factory-filter', $factory],
        ]);
        $this->container->method('has')->willReturnMap([
            [TestAsset\Filter::class . '::ext2', false],
            ['my-factory-filter', true],
        ]);

        /** @var LatteRenderer $renderer */
        $renderer = ($this->factory)($this->container);

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

        $this->container->method('get')->willReturnMap([
            ['config', $config],
            [MultiplePathLoaderInterface::class, $loader],
            [TestAsset\Macro::class, new TestAsset\Macro()],
        ]);
        $this->container->method('has')->with(TestAsset\Macro::class)->willReturn(true);

        /** @var LatteRenderer $renderer */
        $renderer = ($this->factory)($this->container);

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

        $this->container->method('get')->willReturnMap([
            ['config', $config],
            [MultiplePathLoaderInterface::class, $this->createMock(MultiplePathLoaderInterface::class)],
            ['macro', new stdClass()],
        ]);
        $this->container->method('has')->with('macro')->willReturn(true);

        $this->expectException(InvalidMacroException::class);
        $this->expectException(ContainerExceptionInterface::class);
        ($this->factory)($this->container);
    }
}
