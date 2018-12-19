<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_template() : void
    {
        $view = new View('foo');

        $this->assertSame('foo', $view->getTemplate());

        $view = $view->withTemplate('bar');

        $this->assertSame('bar', $view->getTemplate());
    }

    /**
     * @test
     */
    public function it_has_arguments() : void
    {
        $view = new View('template', ['foo' => 'bar']);

        $this->assertSame('bar', $view->getArgument('foo'));
        $this->assertNull($view->getArgument('bar'));
        $this->assertEquals(['foo' => 'bar'], $view->getArguments());

        $view = $view->withArgument('foo', ['baz' => 'qux']);
        $this->assertSame(['baz' => 'qux'], $view->getArgument('foo'));
        $this->assertEquals(['foo' => ['baz' => 'qux']], $view->getArguments());

        $view = $view->withArguments(['foo' => ['quux' => 'quuz']]);
        $this->assertSame(['baz' => 'qux', 'quux' => 'quuz'], $view->getArgument('foo'));
        $this->assertEquals(['foo' => ['baz' => 'qux', 'quux' => 'quuz']], $view->getArguments());
    }
}
