<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener\BuildView;

use Libero\LiberoPageBundle\EventListener\BuildView\ItemRefTeaserHrefListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\UrlGeneratorTestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ItemRefTeaserHrefListenerTest extends TestCase
{
    use UrlGeneratorTestCase;
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_item_ref_element(string $xml) : void
    {
        $listener = new ItemRefTeaserHrefListener($this->createFailingUrlGenerator());

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
        $listener = new ItemRefTeaserHrefListener($this->createFailingUrlGenerator());

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
        $listener = new ItemRefTeaserHrefListener($this->createFailingUrlGenerator());

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
        $listener = new ItemRefTeaserHrefListener($this->createFailingUrlGenerator());

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
    public function it_sets_the_href_argument() : void
    {
        $listener = new ItemRefTeaserHrefListener($this->createDumpingUrlGenerator());

        $element = $this->loadElement('<item-ref xmlns="http://libero.pub" id="id" service="service"/>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser.html.twig', [], ['con' => 'text'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame(['href' => 'libero.page.content.service/{"id":"id"}'], $view->getArguments());
    }
}
