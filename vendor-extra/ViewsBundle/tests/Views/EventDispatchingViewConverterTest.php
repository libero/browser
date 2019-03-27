<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Text;
use Libero\ViewsBundle\Event\CreateViewEvent;
use Libero\ViewsBundle\Views\EventDispatchingViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use tests\Libero\ContentPageBundle\XmlTestCase;

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
        $handler = new EventDispatchingViewConverter(new EventDispatcher());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Expected the template '@LiberoPatterns/text.html.twig' for a non-element node");

        $handler->convert(new Text('foo'), '@LiberoPatterns/not-text.html.twig');
    }

    /**
     * @test
     */
    public function it_dispatches_an_event() : void
    {
        $dispatcher = new EventDispatcher();
        $handler = new EventDispatchingViewConverter($dispatcher);

        $node = new Element('element');

        $dispatcher->addListener(
            CreateViewEvent::NAME,
            function (CreateViewEvent $event) use ($node) : void {
                $this->assertEquals($node, $event->getObject());
                $this->assertEquals(new View('template', [], ['con' => 'text']), $event->getView());

                $event->setView(new View('changed'));
            }
        );

        $view = $handler->convert($node, 'template', ['con' => 'text']);

        $this->assertEquals('changed', $view->getTemplate());
    }
}
