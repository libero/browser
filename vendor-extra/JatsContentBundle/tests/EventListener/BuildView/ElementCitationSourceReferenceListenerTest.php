<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\ElementCitationSourceReferenceListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ElementCitationSourceReferenceListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_element_citation_element(string $xml) : void
    {
        $listener = new ElementCitationSourceReferenceListener($this->createFailingConverter());

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
        $listener = new ElementCitationSourceReferenceListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<element-citation xmlns="http://jats.nlm.nih.gov">
    <source>Foo</source>
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
    public function it_does_nothing_if_there_is_no_source() : void
    {
        $listener = new ElementCitationSourceReferenceListener($this->createFailingConverter());

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
    public function it_does_nothing_if_there_is_already_details_set() : void
    {
        $listener = new ElementCitationSourceReferenceListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<element-citation xmlns="http://jats.nlm.nih.gov">
    <source>Foo</source>
</element-citation>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/reference.html.twig', ['details' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference.html.twig', $view->getTemplate());
        $this->assertSame(['details' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_details_argument() : void
    {
        $listener = new ElementCitationSourceReferenceListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:element-citation xmlns:jats="http://jats.nlm.nih.gov">
    <jats:source>Foo</jats:source>
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
                'details' => [
                    'text' => new TemplateView(
                        '',
                        [
                            'node' => '/jats:element-citation/jats:source',
                            'template' => '@LiberoPatterns/link.html.twig',
                            'context' => ['bar' => 'baz'],
                        ]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['bar' => 'baz'], $view->getContext());
    }
}
