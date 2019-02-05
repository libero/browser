<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\ViewConverter\CallbackVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use PHPUnit\Framework\TestCase;

final class CallbackVisitorTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_view_converter_visitor() : void
    {
        $handler = new CallbackVisitor(
            function (Element $element, View $view, array &$context) : View {
                return $view;
            }
        );

        $this->assertInstanceOf(ViewConverterVisitor::class, $handler);
    }

    /**
     * @test
     */
    public function it_returns_the_results_of_a_callback() : void
    {
        $element = new Element('foo');
        $context = ['bar' => 'baz'];

        $handler = new CallbackVisitor(
            function (Element $element, View $view, array &$context) : View {
                $context['element'] = $element;

                return $view->withTemplate('template');
            }
        );

        $this->assertEquals(new View('template'), $handler->visit($element, new View(null), $context));
        $this->assertEquals(['bar' => 'baz', 'element' => $element], $context);
    }
}
