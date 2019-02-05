<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Text;
use Libero\ViewsBundle\ViewConverter\CallbackVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterRegistry;
use LogicException;
use PHPUnit\Framework\TestCase;

final class ViewConverterRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_view_converter() : void
    {
        $handler = new ViewConverterRegistry();

        $this->assertInstanceOf(ViewConverter::class, $handler);
    }

    /**
     * @test
     */
    public function it_returns_text_by_default() : void
    {
        $handler = new ViewConverterRegistry();

        $xml = FluentDOM::load('<foo>bar <baz>qux</baz> quux</foo>');
        /** @var Element $node */
        $node = $xml->documentElement;

        $expected = new View(
            '@LiberoPatterns/text.html.twig',
            ['nodes' => 'bar qux quux']
        );

        $this->assertEquals($expected, $handler->convert($node));
    }

    /**
     * @test
     */
    public function it_fails_if_non_elements_try_to_use_a_pattern() : void
    {
        $handler = new ViewConverterRegistry();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Expected the template '@LiberoPatterns/text.html.twig' for a non-element node");

        $handler->convert(new Text('foo'), '@LiberoPatterns/not-text.html.twig');
    }

    /**
     * @test
     */
    public function it_delegates_to_visitors() : void
    {
        $handler = new ViewConverterRegistry();

        $handler->add(
            new CallbackVisitor(
                function (Element $element, View $view, array &$context = []) : View {
                    return $view->withTemplate($view->getTemplate().'foo')->withArgument('foo', 'foo');
                }
            ),
            new CallbackVisitor(
                function (Element $element, View $view, array &$context = []) : View {
                    return $view->withTemplate($view->getTemplate().'bar')->withArgument('bar', 'bar');
                }
            )
        );

        $handler->add(
            new CallbackVisitor(
                function (Element $element, View $view, array &$context = []) : View {
                    return $view->withTemplate($view->getTemplate().'baz')->withArgument('baz', 'baz');
                }
            )
        );

        $this->assertEquals(
            new View('foobarbaz', ['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz']),
            $handler->convert(new Element('foo'), null, [])
        );
    }
}
