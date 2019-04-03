<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Views\StringView;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class StringViewTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_view() : void
    {
        $view = new StringView('text');

        $this->assertInstanceOf(View::class, $view);
    }

    /**
     * @test
     */
    public function it_is_a_string() : void
    {
        $view = new StringView('text');

        $this->assertSame('text', (string) $view);
    }

    /**
     * @test
     */
    public function it_has_context() : void
    {
        $view = new StringView('text', ['foo' => 'bar']);

        $this->assertTrue($view->hasContext('foo'));
        $this->assertFalse($view->hasContext('bar'));

        $this->assertSame('bar', $view->getContext('foo'));
        $this->assertNull($view->getContext('bar'));
        $this->assertEquals(['foo' => 'bar'], $view->getContext());
    }
}
