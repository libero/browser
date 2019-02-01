<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\CallbackInlineViewConverter;
use Libero\ViewsBundle\Views\InlineViewConverter;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class CallbackInlineViewConverterTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_inline_view_converter() : void
    {
        $handler = new CallbackInlineViewConverter(
            function () : View {
                return new View(null);
            }
        );

        $this->assertInstanceOf(InlineViewConverter::class, $handler);
    }

    /**
     * @test
     */
    public function it_returns_the_results_of_a_callback() : void
    {
        $handler = new CallbackInlineViewConverter(
            function (NonDocumentTypeChildNode $object, array $context = []) : View {
                return new View(null, $context + ['object' => $object]);
            }
        );

        $object = new Element('foo');
        $expected = new View(null, ['bar' => 'baz', 'object' => $object]);

        $this->assertEquals($expected, $handler->convert($object, ['bar' => 'baz']));
    }
}
