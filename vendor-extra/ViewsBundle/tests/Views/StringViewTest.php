<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Views\StringView;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\json_encode;

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

    /**
     * @test
     */
    public function it_is_json_serializable() : void
    {
        $view = new StringView('text');

        $this->assertSame('"text"', json_encode($view));
    }
}
