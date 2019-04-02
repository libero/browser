<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Text;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\EmptyView;
use Libero\ViewsBundle\Views\EventDispatchingViewConverter;
use Libero\ViewsBundle\Views\StringView;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class EventDispatchingViewConverterTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     */
    public function it_is_a_view_converter() : void
    {
        $handler = new EventDispatchingViewConverter(new EventDispatcher());

        $this->assertInstanceOf(ViewConverter::class, $handler);
    }

    /**
     * @test
     */
    public function it_returns_text_by_default() : void
    {
        $handler = new EventDispatchingViewConverter(new EventDispatcher());

        $node = $this->loadElement('<foo>bar <baz>qux</baz> quux</foo>');

        $expected = new StringView('bar qux quux', ['con' => 'text']);

        $this->assertEquals($expected, $handler->convert($node, null, ['con' => 'text']));
    }

    /**
     * @test
     * @dataProvider nonElementProvider
     */
    public function it_handles_non_elements(string $node, View $expected) : void
    {
        $handler = new EventDispatchingViewConverter(new EventDispatcher());

        $node = $this->loadNode($node);

        $this->assertEquals($expected, $handler->convert($node, null, ['con' => 'text']));
    }

    public function nonElementProvider() : iterable
    {
        yield 'cdata' => ['<![CDATA[<cdata>]]>', new StringView('<cdata>', ['con' => 'text'])];
        yield 'comment' => ['<!--comment-->', new EmptyView(['con' => 'text'])];
        yield 'processing instruction' => ['<?processing instruction?>', new EmptyView(['con' => 'text'])];
        yield 'text' => ['text', new StringView('text', ['con' => 'text'])];
    }

    /**
     * @test
     */
    public function it_fails_if_non_elements_try_to_use_a_pattern() : void
    {
        $handler = new EventDispatchingViewConverter(new EventDispatcher());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Expected no template for a non-element node');

        $handler->convert(new Text('foo'), '@LiberoPatterns/template.html.twig');
    }

    /**
     * @test
     */
    public function it_dispatches_an_event() : void
    {
        $dispatcher = new EventDispatcher();
        $handler = new EventDispatchingViewConverter($dispatcher);

        $node = new Element('element');

        $expected = new TemplateView('changed', ['one' => 'two'], ['three' => 'four']);

        $dispatcher->addListener(
            BuildViewEvent::NAME,
            function (BuildViewEvent $event) use ($expected, $node) : void {
                $this->assertEquals($node, $event->getObject());
                $this->assertEquals(new TemplateView('template', [], ['con' => 'text']), $event->getView());

                $event->setView($expected);
            }
        );

        $view = $handler->convert($node, 'template', ['con' => 'text']);

        $this->assertEquals($expected, $view);
    }
}
