<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\FigCaptionTitleFigureListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FigCaptionTitleFigureListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_fig_element(string $xml) : void
    {
        $listener = new FigCaptionTitleFigureListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView(null));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<fig xmlns="http://example.com"><caption><title>foo</title></caption></fig>'];
        yield 'different element' => ['<italic xmlns="http://jats.nlm.nih.gov">foo</italic>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_figure_template() : void
    {
        $listener = new FigCaptionTitleFigureListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fig xmlns="http://jats.nlm.nih.gov">
    <caption>
        <title>foo</title>
    </caption>
</fig>
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
    public function it_does_nothing_if_there_is_no_caption_title() : void
    {
        $listener = new FigCaptionTitleFigureListener($this->createFailingConverter());

        $element = $this->loadElement('<fig xmlns="http://jats.nlm.nih.gov"><p>foo</p></fig>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/figure.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/figure.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_caption_heading_set() : void
    {
        $listener = new FigCaptionTitleFigureListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fig xmlns="http://jats.nlm.nih.gov">
    <caption>
        <title>foo</title>
    </caption>
</fig>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/figure.html.twig', ['caption' => ['heading' => 'bar']])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/figure.html.twig', $view->getTemplate());
        $this->assertSame(['caption' => ['heading' => 'bar']], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_caption_heading_argument() : void
    {
        $listener = new FigCaptionTitleFigureListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:fig xmlns:jats="http://jats.nlm.nih.gov">
    <jats:caption>
        <jats:title>Title</jats:title>
        <jats:p>Paragraph</jats:p>
    </jats:caption>
</jats:fig>
XML
        );

        $context = ['bar' => 'baz'];

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/figure.html.twig', [], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/figure.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'caption' => [
                    'heading' => [
                        'node' => '/jats:fig/jats:caption/jats:title',
                        'template' => '@LiberoPatterns/heading.html.twig',
                        'context' => ['bar' => 'baz'],
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['bar' => 'baz', 'level' => 2], $view->getContext());
    }
}
