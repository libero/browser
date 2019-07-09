<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\ElementCitationArticleTitleReferenceListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ElementCitationArticleTitleReferenceListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_element_citation_element(string $xml) : void
    {
        $listener = new ElementCitationArticleTitleReferenceListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/reference.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<element-citation xmlns="http://example.com">foo</element-citation>'];
        yield 'different element' => ['<mixed-citation xmlns="http://jats.nlm.nih.gov">foo</mixed-citation>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_reference_template() : void
    {
        $listener = new ElementCitationArticleTitleReferenceListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<element-citation xmlns="http://jats.nlm.nih.gov">
    <article-title>foo</article-title>
</element-citation>
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
    public function it_does_nothing_if_there_is_no_article_title() : void
    {
        $listener = new ElementCitationArticleTitleReferenceListener($this->createFailingConverter());

        $element = $this->loadElement('<element-citation xmlns="http://jats.nlm.nih.gov"/>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/reference.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_heading_set() : void
    {
        $listener = new ElementCitationArticleTitleReferenceListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<element-citation xmlns="http://jats.nlm.nih.gov">
    <article-title>foo</article-title>
</element-citation>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/reference.html.twig', ['heading' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference.html.twig', $view->getTemplate());
        $this->assertSame(['heading' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_heading_argument() : void
    {
        $listener = new ElementCitationArticleTitleReferenceListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:element-citation xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-title>foo</jats:article-title>
</jats:element-citation>
XML
        );

        $context = ['bar' => 'baz'];

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/reference.html.twig', [], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'heading' => [
                    'node' => '/jats:element-citation/jats:article-title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['bar' => 'baz'],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['bar' => 'baz'], $view->getContext());
    }
}
