<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use PHPUnit\Framework\TestCase;

final class CallbackViewConverterTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_view_converter() : void
    {
        $handler = new CallbackViewConverter(
            function () : View {
                return new View(null);
            }
        );

        $this->assertInstanceOf(ViewConverter::class, $handler);
    }

    /**
     * @test
     */
    public function it_returns_the_results_of_a_callback() : void
    {
        $element = new Element('foo');

        $handler = new CallbackViewConverter(
            function (Element $object, ?string $template, array $context = []) use ($element) : View {
                return new View('template', $context + ['element' => $element]);
            }
        );

        $expected = new View('template', ['bar' => 'baz', 'element' => $element]);

        $this->assertEquals($expected, $handler->convert($element, 'template', ['bar' => 'baz']));
    }
}
