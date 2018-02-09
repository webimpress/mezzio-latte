<?php

declare(strict_types=1);

namespace ZendTest\Expressive\Latte;

use Generator;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zend\Expressive\Latte\Exception\UnknownNamespaceException;
use Zend\Expressive\Latte\MultipleFileLoader;
use Zend\Expressive\Latte\MultiplePathLoaderInterface;

class MultipleFileLoaderTest extends TestCase
{
    public function testInstanceOfMultiplePathLoaderInterfaceAndDefaultFileExtension() : void
    {
        $loader = new MultipleFileLoader();

        self::assertInstanceOf(MultiplePathLoaderInterface::class, $loader);
        self::assertAttributeSame('latte', 'fileExtension', $loader);
    }

    public function testWithCustomFileExtension() : void
    {
        $loader = new MultipleFileLoader('foo-ext.bar');

        self::assertAttributeSame('foo-ext.bar', 'fileExtension', $loader);
    }

    public function testPaths() : void
    {
        $loader = new MultipleFileLoader();
        $loader->addPath('foo/bar', 'Ns1');
        $loader->addPath('baz/goo');

        $paths = $loader->getPaths();

        self::assertInternalType('array', $paths);
        self::assertSame([
            'Ns1' => 'foo/bar',
            null => 'baz/goo',
        ], $paths);
    }

    public function method() : Generator
    {
        yield ['getContent'];
        yield ['isExpired', [time()]];
        yield ['isExpired', [time() - 1]];
        yield ['isExpired', [time() + 1]];
        yield ['getUniqueId'];
    }

    /**
     * @dataProvider method
     */
    public function testExceptionWhenNamespaceIsNotDefined(string $method, array $args = []) : void
    {
        $loader = new MultipleFileLoader();
        array_unshift($args, 'foo');

        $this->expectException(UnknownNamespaceException::class);
        call_user_func_array([$loader, $method], $args);
    }

    public function testGetContentThrowsExceptionWhenTemplateDoesNotExist() : void
    {
        $loader = new MultipleFileLoader();
        $loader->addPath(__DIR__);

        $this->expectException(RuntimeException::class);
        $loader->getContent('late-foo-template');
    }

    public function testGetContent() : void
    {
        $root = vfsStream::setup(__FUNCTION__);
        $dirNS = vfsStream::newDirectory('ns')->at($root);
        vfsStream::newFile('my-template.latte')
            ->at($dirNS)
            ->setContent('template content');

        $dirInner = vfsStream::newDirectory('inner')->at($dirNS);
        vfsStream::newFile('tmp.latte')
            ->at($dirInner)
            ->setContent('$Inner $Template');

        $dirNoNS = vfsStream::newDirectory('no-ns')->at($root);
        vfsStream::newFile('other.latte')
            ->at($dirNoNS)
            ->setContent('|other template|');

        $loader = new MultipleFileLoader('latte');
        $loader->addPath($dirNS->url(), 'ns');
        $loader->addPath($dirNoNS->url());

        self::assertSame('template content', $loader->getContent('ns::my-template'));
        self::assertSame('$Inner $Template', $loader->getContent('ns::inner/tmp'));
        self::assertSame('|other template|', $loader->getContent('other'));
    }

    public function testIsExpired() : void
    {
        $root = vfsStream::setup(__FUNCTION__);
        vfsStream::newFile('index.latte')->at($root);

        $loader = new MultipleFileLoader('latte');
        $loader->addPath($root->url());

        self::assertTrue($loader->isExpired('index', time() - 1));
        self::assertFalse($loader->isExpired('index', time()));
        self::assertFalse($loader->isExpired('index', time() + 1));
    }

    public function testGetReferredName() : void
    {
        $loader = new MultipleFileLoader('myext');
        $loader->addPath('path/foo', 'ns');

        self::assertSame('ns::baz', $loader->getReferredName('ns::baz', 'jar/bar/mat'));
        self::assertSame('foo/bar', $loader->getReferredName('foo/bar', 'jar/ok'));
    }

    public function testGetUniqueId() : void
    {
        $loader = new MultipleFileLoader('el');
        $loader->addPath('path/foo', 'ns');

        self::assertSame('path/foo/goo.el', $loader->getUniqueId('ns::goo'));
    }
}
