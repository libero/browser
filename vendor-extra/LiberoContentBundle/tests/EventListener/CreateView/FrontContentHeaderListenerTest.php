<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\EventListener\CreateView;

use Libero\LiberoContentBundle\EventListener\CreateView\FrontContentHeaderListener;
use Libero\ViewsBundle\Event\CreateViewEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class FrontContentHeaderListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_front_element(string $xml) : void
    {
        $listener = new FrontContentHeaderListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new CreateViewEvent($element, new View('@LiberoPatterns/content-header.html.twig'));
        $listener->onCreateView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<front xmlns="http://example.com">foo</front>'];
        yield 'different element' => ['<foo xmlns="http://libero.pub">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_content_header_template() : void
    {
        $listener = new FrontContentHeaderListener($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://libero.pub"><title>foo</title></front>');

        $event = new CreateViewEvent($element, new View('template'));
        $listener->onCreateView($event);
        $view = $event->getView();

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_title() : void
    {
        $listener = new FrontContentHeaderListener($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://libero.pub">foo</front>');

        $event = new CreateViewEvent($element, new View('@LiberoPatterns/content-header.html.twig'));
        $listener->onCreateView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_content_title_set() : void
    {
        $listener = new FrontContentHeaderListener($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://libero.pub"><title>foo</title></front>');

        $event = new CreateViewEvent(
            $element,
            new View('@LiberoPatterns/content-header.html.twig', ['contentTitle' => 'bar'])
        );
        $listener->onCreateView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertSame(['contentTitle' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_text_argument() : void
    {
        $listener = new FrontContentHeaderListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<libero:front xmlns:libero="http://libero.pub">
    <libero:title>foo</libero:title>
</libero:front>
XML
        );

        $context = ['bar' => 'baz'];

        $event = new CreateViewEvent($element, new View('@LiberoPatterns/content-header.html.twig', [], $context));
        $listener->onCreateView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'contentTitle' => [
                    'node' => '/libero:front/libero:title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['bar' => 'baz'],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['bar' => 'baz'], $view->getContext());
    }
}
