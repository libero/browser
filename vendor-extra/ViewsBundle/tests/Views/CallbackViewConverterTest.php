<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\ViewConverter;
use PHPUnit\Framework\TestCase;

final class CallbackViewConverterTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_view_converter() : void
    {
        $converter = new CallbackViewConverter(
            static function () : TemplateView {
                return new TemplateView('template');
            }
        );

        $this->assertInstanceOf(ViewConverter::class, $converter);
    }

    /**
     * @test
     */
    public function it_returns_the_results_of_a_callback() : void
    {
        $converter = new CallbackViewConverter(
            static function (Element $object, ?string $template, array $context = []) : TemplateView {
                return new TemplateView($template ?? '', $context + ['element' => $object]);
            }
        );

        $element = new Element('foo');
        $expected = new TemplateView('template', ['bar' => 'baz', 'element' => $element]);

        $this->assertEquals($expected, $converter->convert($element, 'template', ['bar' => 'baz']));
    }
}
