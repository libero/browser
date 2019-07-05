<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\ElementCitationDoiReferenceListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ElementCitationDoiReferenceListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_element_citation_element(string $xml) : void
    {
        $listener = new ElementCitationDoiReferenceListener();

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
        $listener = new ElementCitationDoiReferenceListener();

        $element = $this->loadElement(
            <<<XML
<element-citation xmlns="http://jats.nlm.nih.gov">
    <pub-id pub-id-type="doi">10.1000/182</pub-id>
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
    public function it_does_nothing_if_there_is_no_doi() : void
    {
        $listener = new ElementCitationDoiReferenceListener();

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
    public function it_does_nothing_if_there_is_already_a_doi_set() : void
    {
        $listener = new ElementCitationDoiReferenceListener();

        $element = $this->loadElement(
            <<<XML
<element-citation xmlns="http://jats.nlm.nih.gov">
    <pub-id pub-id-type="doi">10.1000/182</pub-id>
</element-citation>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/reference.html.twig', ['doi' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference.html.twig', $view->getTemplate());
        $this->assertSame(['doi' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_doi_argument() : void
    {
        $listener = new ElementCitationDoiReferenceListener();

        $element = $this->loadElement(
            <<<XML
<jats:element-citation xmlns:jats="http://jats.nlm.nih.gov">
    <jats:pub-id pub-id-type="doi">10.1000/182</jats:pub-id>
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
                'doi' => '10.1000/182',
            ],
            $view->getArguments()
        );
        $this->assertSame(['bar' => 'baz'], $view->getContext());
    }
}
