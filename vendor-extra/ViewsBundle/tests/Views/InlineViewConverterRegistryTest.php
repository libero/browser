<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\ViewConverter\CallbackVisitor;
use Libero\ViewsBundle\Views\InlineViewConverter;
use Libero\ViewsBundle\Views\InlineViewConverterRegistry;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class InlineViewConverterRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_inline_view_converter() : void
    {
        $handler = new InlineViewConverterRegistry();

        $this->assertInstanceOf(InlineViewConverter::class, $handler);
    }

    /**
     * @test
     */
    public function it_returns_text_by_default() : void
    {
        $handler = new InlineViewConverterRegistry();

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
    public function it_delegates_to_visitors() : void
    {
        $handler = new InlineViewConverterRegistry();

        $handler->add(
            new CallbackVisitor(
                function (Element $object, View $view, array &$context = []) : View {
                    return $view->withTemplate($view->getTemplate().'foo')->withArgument('foo', 'foo');
                }
            ),
            new CallbackVisitor(
                function (Element $object, View $view, array &$context = []) : View {
                    return $view->withTemplate($view->getTemplate().'bar')->withArgument('bar', 'bar');
                }
            )
        );

        $handler->add(
            new CallbackVisitor(
                function (Element $object, View $view, array &$context = []) : View {
                    return $view->withTemplate($view->getTemplate().'baz')->withArgument('baz', 'baz');
                }
            )
        );

        $this->assertEquals(
            new View('foobarbaz', ['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz']),
            $handler->convert(new Element('foo'), [])
        );
    }
}
