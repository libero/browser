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
    public function it_delegates_to_visitors() : void
    {
        $dispatcher = new EventDispatcher();
        $handler = new EventDispatchingViewConverter($dispatcher);

        $dispatcher->addListener(
            CreateViewEvent::NAME,
            function (CreateViewEvent $event) : void {
                $event->setView(
                    $event->getView()
                        ->withTemplate($event->getView()->getTemplate().'foo')
                        ->withArgument('node', $event->getObject())
                        ->withArgument('foo', 'foo')
                        ->withContext(['one' => 'foo'])
                );
            }
        );

        $dispatcher->addListener(
            CreateViewEvent::NAME,
            function (CreateViewEvent $event) : void {
                $event->setView(
                    $event->getView()
                        ->withTemplate($event->getView()->getTemplate().'bar')
                        ->withArgument('bar', 'bar')
                        ->withContext(['one' => 'bar'])
                );
            }
        );

        $dispatcher->addListener(
            CreateViewEvent::NAME,
            function (CreateViewEvent $event) : void {
                $event->setView(
                    $event->getView()
                        ->withTemplate($event->getView()->getTemplate().'baz')
                        ->withArgument('baz', 'baz')
                        ->withContext(['one' => 'baz'])
                );
            }
        );

        $view = $handler->convert($node = new Element('element'), 'template', ['con' => 'text', 'one' => 'two']);

        $this->assertEquals('templatefoobarbaz', $view->getTemplate());
        $this->assertEquals(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz', 'node' => $node], $view->getArguments());
        $this->assertEquals(['con' => 'text', 'one' => 'baz'], $view->getContext());
    }
}
