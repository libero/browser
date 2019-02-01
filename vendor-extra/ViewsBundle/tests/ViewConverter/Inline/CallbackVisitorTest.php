<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\ViewConverter\Inline;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\ViewConverter\Inline\CallbackVisitor;
use Libero\ViewsBundle\Views\InlineViewConverterVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class CallbackVisitorTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_inline_view_converter_visitor() : void
    {
        $handler = new CallbackVisitor(
            function (NonDocumentTypeChildNode $object, View $view, array &$context = []) : View {
                return $view;
            }
        );

        $this->assertInstanceOf(InlineViewConverterVisitor::class, $handler);
    }

    /**
     * @test
     */
    public function it_returns_the_results_of_a_callback() : void
    {
        $element = new Element('foo');
        $context = ['bar' => 'baz'];

        $handler = new CallbackVisitor(
            function (NonDocumentTypeChildNode $object, View $view, array &$context = []) : View {
                $context['object'] = $object;

                return $view->withTemplate('template');
            }
        );

        $this->assertEquals(new View('template'), $handler->visit($element, new View(null), $context));
        $this->assertEquals(['bar' => 'baz', 'object' => $element], $context);
    }
}
