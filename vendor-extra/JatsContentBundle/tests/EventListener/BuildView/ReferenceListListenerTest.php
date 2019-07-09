<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\ReferenceListListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ReferenceListListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_ref_list_element(string $xml) : void
    {
        $listener = new ReferenceListListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/reference-list.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference-list.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => [
            <<<XML
<ref-list xmlns="http://www.example.com">
    <ref>   
        <element-citation>
            <article-title/>
        </element-citation>
    </ref>
</ref-list>
XML
            ,
        ];
        yield 'different element' => ['<p xmlns="http://jats.nlm.nih.gov">foo</p>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_reference_list_template() : void
    {
        $listener = new ReferenceListListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<ref-list xmlns="http://jats.nlm.nih.gov">
    <ref>   
        <element-citation>
            <article-title/>
        </element-citation>
    </ref>
</ref-list>
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
    public function it_does_nothing_if_there_are_already_items_set() : void
    {
        $listener = new ReferenceListListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<ref-list xmlns="http://jats.nlm.nih.gov">
    <ref>   
        <element-citation>
            <article-title/>
        </element-citation>
    </ref>
</ref-list>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/reference-list.html.twig', ['items' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference-list.html.twig', $view->getTemplate());
        $this->assertSame(['items' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_are_no_element_citations_with_article_titles() : void
    {
        $listener = new ReferenceListListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<ref-list xmlns="http://jats.nlm.nih.gov">
    <ref>   
        <element-citation>
            <source/>
        </element-citation>
    </ref>
</ref-list>
XML
        );

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/reference-list.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference-list.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_items_argument() : void
    {
        $listener = new ReferenceListListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:ref-list xmlns:jats="http://jats.nlm.nih.gov">
    <jats:ref>   
        <jats:element-citation>
            <jats:article-title/>
        </jats:element-citation>
    </jats:ref>
    <jats:ref>   
        <jats:mixed-citation>
            <jats:article-title/>
        </jats:mixed-citation>
    </jats:ref>
    <jats:p/>
    <jats:ref>   
        <jats:element-citation>
            <jats:source/>
        </jats:element-citation>
    </jats:ref>
    <jats:ref>   
        <jats:element-citation>
            <jats:article-title/>
        </jats:element-citation>
    </jats:ref>
</jats:ref-list>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/reference-list.html.twig', [], ['foo' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/reference-list.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'items' => [
                    [
                        'content' => [
                            'node' => '/jats:ref-list/jats:ref[1]/jats:element-citation',
                            'template' => '@LiberoPatterns/reference.html.twig',
                            'context' => ['foo' => 'bar'],
                        ],
                    ],
                    [
                        'content' => [
                            'node' => '/jats:ref-list/jats:ref[4]/jats:element-citation',
                            'template' => '@LiberoPatterns/reference.html.twig',
                            'context' => ['foo' => 'bar'],
                        ],
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['foo' => 'bar'], $view->getContext());
    }
}
