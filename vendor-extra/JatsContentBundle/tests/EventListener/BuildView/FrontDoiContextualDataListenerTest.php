<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\FrontDoiContextualDataListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontDoiContextualDataListenerTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $listener = new FrontDoiContextualDataListener();

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/contextual-data.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/contextual-data.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<front xmlns="http://example.com">foo</front>'];
        yield 'different element' => ['<foo xmlns="http://jats.nlm.nih.gov">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_contextual_data_template() : void
    {
        $listener = new FrontDoiContextualDataListener();

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-id pub-id-type="doi">10.5555/12345678</article-id>
    </article-meta>
</front>
XML
        );

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
    public function it_does_nothing_if_there_is_no_doi() : void
    {
        $listener = new FrontDoiContextualDataListener();

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-id pub-id-type="not-doi">10.5555/12345678</article-id>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/contextual-data.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/contextual-data.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_doi_argument_set() : void
    {
        $listener = new FrontDoiContextualDataListener();

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-id pub-id-type="doi">10.5555/12345678</article-id>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/contextual-data.html.twig', ['doi' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/contextual-data.html.twig', $view->getTemplate());
        $this->assertSame(['doi' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_doi_argument() : void
    {
        $listener = new FrontDoiContextualDataListener();

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-id pub-id-type="doi">10.5555/12345678</article-id>
    </article-meta>
</front>
XML
        );

        $context = ['bar' => 'baz'];

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/contextual-data.html.twig', [], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/contextual-data.html.twig', $view->getTemplate());
        $this->assertEquals(['doi' => '10.5555/12345678'], $view->getArguments());
        $this->assertSame(['bar' => 'baz'], $view->getContext());
    }
}
