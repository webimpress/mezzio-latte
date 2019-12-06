<?php

declare(strict_types=1);

namespace ZendTest\Expressive\Latte;

use Latte\Engine;
use Latte\ILoader;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TypeError;
use Zend\Expressive\Latte\Exception\InvalidLoaderException;
use Zend\Expressive\Latte\LatteRenderer;
use Zend\Expressive\Latte\MultipleFileLoader;
use Zend\Expressive\Latte\MultiplePathLoaderInterface;
use Zend\Expressive\Template\TemplatePath;
use Zend\Expressive\Template\TemplateRendererInterface;

class LatteRendererTest extends TestCase
{
    /**
     * @var Engine|ObjectProphecy
     */
    private $engine;

    protected function setUp() : void
    {
        $this->engine = $this->prophesize(Engine::class);
    }

    public function testCreationThrowsExceptionWhenEngineDoesNotHaveLoader() : void
    {
        $this->expectException(TypeError::class);
        new LatteRenderer($this->engine->reveal());
    }

    public function testCreationThrowsExceptionWHenEngineHasInvalidLoader() : void
    {
        $this->engine->getLoader()->willReturn($this->prophesize(ILoader::class));

        $this->expectException(InvalidLoaderException::class);
        new LatteRenderer($this->engine->reveal());
    }

    public function testInstanceOfTemplateRendererInterface() : void
    {
        $this->engine->getLoader()->willReturn($this->prophesize(MultiplePathLoaderInterface::class));

        $renderer = new LatteRenderer($this->engine->reveal());
        self::assertInstanceOf(TemplateRendererInterface::class, $renderer);
    }

    public function testRenderWithoutDefaultParams() : void
    {
        $this->engine->getLoader()->willReturn($this->prophesize(MultiplePathLoaderInterface::class));
        $this->engine->renderToString('foo::bar', ['p0' => 'v1'])
            ->willReturn('rendered-response-baz')
            ->shouldBeCalledTimes(1);

        $renderer = new LatteRenderer($this->engine->reveal());

        $result = $renderer->render('foo::bar', ['p0' => 'v1']);
        self::assertSame('rendered-response-baz', $result);
    }

    public function testRenderWithDefaultParams() : void
    {
        $this->engine->getLoader()->willReturn($this->prophesize(MultiplePathLoaderInterface::class));
        $this->engine->renderToString('foo::baz', ['p2' => 'v3', 'p' => 'def-boo', 'g' => 'def-hoo'])
            ->willReturn('result-bar')
            ->shouldBeCalledTimes(1);

        $renderer = new LatteRenderer($this->engine->reveal());

        $renderer->addDefaultParam('foo::baz', 'p', 'def-boo');
        $renderer->addDefaultParam('foo::baz', 'g', 'def-hoo');
        $renderer->addDefaultParam('foo::bar', 'other', 'value');
        $result = $renderer->render('foo::baz', ['p2' => 'v3']);
        self::assertSame('result-bar', $result);
    }

    public function testAddPath() : void
    {
        $loader = $this->prophesize(MultiplePathLoaderInterface::class);
        $loader->addPath('foo/path', 'bar/ns')->shouldBeCalledTimes(1);

        $this->engine->getLoader()->willReturn($loader->reveal());

        $renderer = new LatteRenderer($this->engine->reveal());

        $renderer->addPath('foo/path', 'bar/ns');
    }

    public function testGetPaths() : void
    {
        $loader = new MultipleFileLoader();

        $this->engine->getLoader()->willReturn($loader);

        $renderer = new LatteRenderer($this->engine->reveal());
        $renderer->addPath('foo.path', 'BAR');
        $renderer->addPath('baz', 'abc');

        $paths = $renderer->getPaths();
        self::assertIsArray($paths);
        self::assertCount(2, $paths);
        self::assertContainsOnlyInstancesOf(TemplatePath::class, $paths);
        self::assertSame('foo.path', $paths[0]->getPath());
        self::assertSame('BAR', $paths[0]->getNamespace());
        self::assertSame('baz', $paths[1]->getPath());
        self::assertSame('abc', $paths[1]->getNamespace());
    }
}
