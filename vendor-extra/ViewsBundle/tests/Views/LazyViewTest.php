<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use ArrayAccess;
use BadMethodCallException;
use Libero\ViewsBundle\Views\LazyView;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use LogicException;
use PHPUnit\Framework\TestCase;
use Traversable;
use function iterator_to_array;

final class LazyViewTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_view() : void
    {
        $view = new LazyView(
            function () : TemplateView {
                throw new LogicException();
            }
        );

        $this->assertInstanceOf(View::class, $view);
    }

    /**
     * @test
     */
    public function it_has_context() : void
    {
        $view = new LazyView(
            function () : TemplateView {
                throw new LogicException();
            },
            ['foo' => 'bar']
        );

        $this->assertTrue($view->hasContext('foo'));
        $this->assertFalse($view->hasContext('bar'));

        $this->assertSame('bar', $view->getContext('foo'));
        $this->assertNull($view->getContext('bar'));
        $this->assertEquals(['foo' => 'bar'], $view->getContext());
    }

    /**
     * @test
     */
    public function it_is_array_accessible() : void
    {
        $view = new LazyView(
            function () : TemplateView {
                return new TemplateView('template', ['foo' => 'bar', 'baz' => ['qux']]);
            }
        );

        $this->assertInstanceOf(ArrayAccess::class, $view);

        $this->assertArrayHasKey('template', $view);
        $this->assertSame('template', $view['template']);
        $this->assertArrayHasKey('arguments', $view);
        $this->assertSame(['foo' => 'bar', 'baz' => ['qux']], $view['arguments']);
        $this->assertArrayNotHasKey('quux', $view);
        $this->assertNull($view['quux']);
    }

    /**
     * @test
     * @dataProvider immutableProvider
     */
    public function it_is_immutable(callable $action) : void
    {
        $view = new LazyView(
            function () : TemplateView {
                throw new LogicException();
            }
        );

        $this->expectException(BadMethodCallException::class);

        $action($view);
    }

    public function immutableProvider() : iterable
    {
        yield 'set' => [
            function (LazyView $view) : void {
                $view['foo'] = 'bar';
            },
        ];
        yield 'unset' => [
            function (LazyView $view) : void {
                unset($view['foo']);
            },
        ];
    }

    /**
     * @test
     */
    public function it_is_traversable() : void
    {
        $view = new LazyView(
            function () : TemplateView {
                return new TemplateView('template', ['foo' => 'bar', 'baz' => ['qux']]);
            }
        );

        $this->assertInstanceOf(Traversable::class, $view);

        $expected = [
            'template' => 'template',
            'arguments' => [
                'foo' => 'bar',
                'baz' => ['qux'],
            ],
        ];

        $this->assertSame($expected, iterator_to_array($view));
    }
}
