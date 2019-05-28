<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\ParagraphListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Event\ChooseTemplateEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ParagraphListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider templateChoiceProvider
     */
    public function it_can_choose_a_template(string $xml, ?string $expected) : void
    {
        $listener = new ParagraphListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new ChooseTemplateEvent($element);
        $listener->onChooseTemplate($event);

        $this->assertSame($expected, $event->getTemplate());
    }

    public function templateChoiceProvider() : iterable
    {
        yield 'p element' => ['<p xmlns="http://jats.nlm.nih.gov">foo</p>', '@LiberoPatterns/paragraph.html.twig'];
        yield 'different namespace' => ['<p xmlns="http://example.com">foo</p>', null];
        yield 'different element' => ['<sec xmlns="http://jats.nlm.nih.gov">foo</sec>', null];
    }

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_p_element(string $xml) : void
    {
        $listener = new ParagraphListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/paragraph.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/paragraph.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<p xmlns="http://example.com">foo</p>'];
        yield 'different element' => ['<sec xmlns="http://jats.nlm.nih.gov">foo</sec>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_paragraph_template() : void
    {
        $listener = new ParagraphListener($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

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
    public function it_does_nothing_if_there_is_already_text_set() : void
    {
        $listener = new ParagraphListener($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/paragraph.html.twig', ['text' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/paragraph.html.twig', $view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_text_argument() : void
    {
        $listener = new ParagraphListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:p xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:bold>bar</jats:bold> baz
</jats:p>
XML
        );

        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/paragraph.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/paragraph.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new TemplateView(
                        '',
                        ['node' => '/jats:p/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new TemplateView(
                        '',
                        ['node' => '/jats:p/jats:bold', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new TemplateView(
                        '',
                        ['node' => '/jats:p/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
