<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Views\EmptyView;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class EmptyViewTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_view() : void
    {
        $view = new EmptyView();

        $this->assertInstanceOf(View::class, $view);
    }

    /**
     * @test
     */
    public function it_casts_to_a_string() : void
    {
        $view = new EmptyView();

        $this->assertSame('', (string) $view);
    }

    /**
     * @test
     */
    public function it_has_context() : void
    {
        $view = new EmptyView(['foo' => 'bar']);

        $this->assertTrue($view->hasContext('foo'));
        $this->assertFalse($view->hasContext('bar'));

        $this->assertSame('bar', $view->getContext('foo'));
        $this->assertNull($view->getContext('bar'));
        $this->assertEquals(['foo' => 'bar'], $view->getContext());
    }
}
