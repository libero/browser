<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\json_encode;

final class ViewTest extends TestCase
{
    /**
     * @test
     */
    public function it_may_have_a_template() : void
    {
        $with = new View('foo');
        $withOut = new View(null);

        $this->assertSame('foo', $with->getTemplate());
        $this->assertNull($withOut->getTemplate());

        $with = $with->withTemplate('bar');

        $this->assertSame('bar', $with->getTemplate());
    }

    /**
     * @test
     */
    public function it_has_arguments() : void
    {
        $view = new View(null, ['foo' => 'bar']);

        $this->assertTrue($view->hasArgument('foo'));
        $this->assertFalse($view->hasArgument('bar'));

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

    /**
     * @test
     */
    public function it_is_json_serializable() : void
    {
        $view = new View('template', ['foo' => 'bar', 'baz' => ['qux']]);

        $expected = json_encode(
            [
                'template' => 'template',
                'arguments' => [
                    'foo' => 'bar',
                    'baz' => ['qux'],
                ],
            ]
        );

        $this->assertJsonStringEqualsJsonString($expected, json_encode($view));
    }
}
