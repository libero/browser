<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener\BuildView;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Libero\LiberoPageBundle\EventListener\BuildView\ItemRefTeaserListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\LazyView;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\GuzzleTestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ItemRefTeaserListenerTest extends TestCase
{
    use GuzzleTestCase;
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_item_ref_element(string $xml) : void
    {
        $listener = new ItemRefTeaserListener($this->client, $this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/teaser.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<item-ref xmlns="http://example.com" id="id" service="service"/>'];
        yield 'different element' => ['<not-ref xmlns="http://libero.pub" id="id" service="service"/>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_teaser_template() : void
    {
        $listener = new ItemRefTeaserListener($this->client, $this->createFailingConverter());

        $element = $this->loadElement('<item-ref xmlns="http://libero.pub" id="id" service="service"/>');

        $event = new BuildViewEvent($element, new TemplateView('template'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_id() : void
    {
        $listener = new ItemRefTeaserListener($this->client, $this->createFailingConverter());

        $element = $this->loadElement('<item-ref xmlns="http://libero.pub" service="service"/>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/teaser.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_service() : void
    {
        $listener = new ItemRefTeaserListener($this->client, $this->createFailingConverter());

        $element = $this->loadElement('<item-ref xmlns="http://libero.pub" id="id"/>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/teaser.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_loads_the_item_and_converts_to_a_teaser() : void
    {
        $listener = new ItemRefTeaserListener($this->client, $this->createDumpingConverter());

        $element = $this->loadElement('<item-ref xmlns="http://libero.pub" id="id" service="service"/>');

        $this->mock->save(
            new Request(
                'GET',
                'service/items/id/versions/latest',
                ['Accept' => 'application/xml']
            ),
            new Response(
                200,
                [],
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>id</libero:id>
        <libero:service>service</libero:service>
    </libero:meta>
</libero:item>
XML
            )
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser.html.twig', ['arg' => 'ument'], ['con' => 'text'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(LazyView::class, $view);
        $this->assertSame(
            [
                'arg' => 'ument',
                'node' => '/libero:item',
                'template' => '@LiberoPatterns/teaser.html.twig',
                'context' => ['con' => 'text'],
            ],
            $view['arguments']
        );
        $this->assertSame(['con' => 'text'], $view->getContext());
    }
}
