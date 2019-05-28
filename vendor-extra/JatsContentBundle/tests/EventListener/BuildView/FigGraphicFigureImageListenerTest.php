<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use DOMElement;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\JatsContentBundle\EventListener\BuildView\FigGraphicFigureImageListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Event\ChooseTemplateEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FigGraphicFigureImageListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider templateChoiceProvider
     */
    public function it_can_choose_a_template(string $xml, ?string $expected) : void
    {
        $listener = new FigGraphicFigureImageListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new ChooseTemplateEvent($element);
        $listener->onChooseTemplate($event);

        $this->assertSame($expected, $event->getTemplate());
    }

    public function templateChoiceProvider() : iterable
    {
        yield 'figure element' => ['<fig xmlns="http://jats.nlm.nih.gov"/>', '@LiberoPatterns/figure.html.twig'];
        yield 'different namespace' => ['<fig xmlns="http://example.com"/>', null];
        yield 'different element' => ['<italic xmlns="http://jats.nlm.nih.gov"/>', null];
    }

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_fig_element(string $xml) : void
    {
        $listener = new FigGraphicFigureImageListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/figure.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/figure.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<fig xmlns="http://example.com"><graphic/></fig>'];
        yield 'different element' => ['<italic xmlns="http://jats.nlm.nih.gov">foo</italic>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_figure_template() : void
    {
        $listener = new FigGraphicFigureImageListener($this->createFailingConverter());

        $element = $this->loadElement('<fig xmlns="http://jats.nlm.nih.gov"><graphic/></fig>');

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
    public function it_does_nothing_if_there_is_already_a_content_argument_set() : void
    {
        $listener = new FigGraphicFigureImageListener($this->createFailingConverter());

        $element = $this->loadElement('<fig xmlns="http://jats.nlm.nih.gov"><graphic/></fig>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/figure.html.twig', ['content' => 'foo'])
        );

        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/figure.html.twig', $view->getTemplate());
        $this->assertSame(['content' => 'foo'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_no_graphics_convert() : void
    {
        $listener = new FigGraphicFigureImageListener(
            $this->createFilteringConverter(
                $this->createFailingConverter(),
                function () : bool {
                    return false;
                }
            )
        );

        $element = $this->loadElement(
            <<<XML
<jats:fig xmlns:jats="http://jats.nlm.nih.gov">
    <jats:graphic/>
    <jats:graphic/>
    <jats:graphic/>
</jats:fig>
XML
        );
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
    public function it_sets_the_content_argument() : void
    {
        $listener = new FigGraphicFigureImageListener(
            $this->createFilteringConverter(
                $this->createDumpingConverter(),
                function (NonDocumentTypeChildNode $node, ?string $template, array $context) : bool {
                    return $node->previousElementSibling instanceof DOMElement;
                }
            )
        );

        $element = $this->loadElement(
            <<<XML
<jats:fig xmlns:jats="http://jats.nlm.nih.gov">
    <jats:graphic/>
    <jats:graphic/>
    <jats:graphic/>
</jats:fig>
XML
        );
        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/figure.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/figure.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'content' => new TemplateView(
                    '',
                    [
                            'node' => '/jats:fig/jats:graphic[2]',
                            'template' => '@LiberoPatterns/image.html.twig',
                            'context' => ['qux' => 'quux'],
                        ]
                ),
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
