<?php

declare(strict_types=1);

namespace WebimpressTest\Mezzio\Latte;

use Latte\Engine;
use latte\iloader;
use Mezzio\Template\TemplatePath;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TypeError;
use Webimpress\Mezzio\Latte\Exception\InvalidLoaderException;
use Webimpress\Mezzio\Latte\LatteRenderer;
use Webimpress\Mezzio\Latte\MultipleFileLoader;
use Webimpress\Mezzio\Latte\MultiplePathLoaderInterface;

class LatteRendererTest extends TestCase
{
    /** @var Engine|MockObject */
    private $engine;

    protected function setUp() : void
    {
        $this->engine = $this->createMock(Engine::class);
    }

    public function testCreationThrowsExceptionWhenEngineDoesNotHaveLoader() : void
    {
        $this->engine->method('getLoader')->willReturn(null);

        $this->expectException(TypeError::class);
        new LatteRenderer($this->engine);
    }

    public function testCreationThrowsExceptionWhenEngineHasInvalidLoader() : void
    {
        $this->engine->method('getLoader')->willReturn($this->createMock(iloader::class));

        $this->expectException(InvalidLoaderException::class);
        new LatteRenderer($this->engine);
    }

    public function testInstanceOfTemplateRendererInterface() : void
    {
        $this->engine->method('getLoader')->willReturn($this->createMock(MultiplePathLoaderInterface::class));

        $renderer = new LatteRenderer($this->engine);
        self::assertInstanceOf(TemplateRendererInterface::class, $renderer);
    }

    public function testRenderWithoutDefaultParams() : void
    {
        $this->engine->method('getLoader')
                     ->willReturn($this->createMock(MultiplePathLoaderInterface::class));
        $this->engine->expects(self::once())
                     ->method('renderToString')
                     ->with('foo::bar', ['p0' => 'v1'])
                     ->willReturn('rendered-response-baz');

        $renderer = new LatteRenderer($this->engine);

        $result = $renderer->render('foo::bar', ['p0' => 'v1']);
        self::assertSame('rendered-response-baz', $result);
    }

    public function testRenderWithDefaultParams() : void
    {
        $this->engine->method('getLoader')
                     ->willReturn($this->createMock(MultiplePathLoaderInterface::class));
        $this->engine->expects(self::once())
                     ->method('renderToString')
                     ->with('foo::baz', ['p2' => 'v3', 'p' => 'def-boo', 'g' => 'def-hoo'])
                     ->willReturn('result-bar');

        $renderer = new LatteRenderer($this->engine);

        $renderer->addDefaultParam('foo::baz', 'p', 'def-boo');
        $renderer->addDefaultParam('foo::baz', 'g', 'def-hoo');
        $renderer->addDefaultParam('foo::bar', 'other', 'value');
        $result = $renderer->render('foo::baz', ['p2' => 'v3']);
        self::assertSame('result-bar', $result);
    }

    public function testAddPath() : void
    {
        $loader = $this->createMock(MultiplePathLoaderInterface::class);
        $loader->expects(self::once())->method('addPath')->with('foo/path', 'bar/ns');

        $this->engine->method('getLoader')->willReturn($loader);

        $renderer = new LatteRenderer($this->engine);

        $renderer->addPath('foo/path', 'bar/ns');
    }

    public function testGetPaths() : void
    {
        $loader = new MultipleFileLoader();

        $this->engine->method('getLoader')->willReturn($loader);

        $renderer = new LatteRenderer($this->engine);
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
