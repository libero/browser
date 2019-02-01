<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\ViewConverter\CallbackVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterRegistry;
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
    public function it_delegates_to_visitors() : void
    {
        $handler = new ViewConverterRegistry();

        $this->assertEquals(new View(null), $handler->convert(new Element('foo'), null, []));

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
            $handler->convert(new Element('foo'), null, [])
        );
    }
}
