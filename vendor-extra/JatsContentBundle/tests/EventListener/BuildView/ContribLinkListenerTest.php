<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\ContribLinkListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ContribLinkListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_contrib_element(string $xml) : void
    {
        $listener = new ContribLinkListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => [
            <<<XML
<contrib xmlns="http://example.com">
    <name>
        <given-names>foo</given-names>
    </name>
</contrib>
XML
            ,
        ];
        yield 'different element' => ['<italic xmlns="http://jats.nlm.nih.gov">foo</italic>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_link_template() : void
    {
        $listener = new ContribLinkListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<contrib xmlns="http://jats.nlm.nih.gov">
    <name>
        <given-names>foo</given-names>
    </name>
</contrib>
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
    public function it_does_nothing_if_there_is_no_name() : void
    {
        $listener = new ContribLinkListener($this->createFailingConverter());

        $element = $this->loadElement('<contrib xmlns="http://jats.nlm.nih.gov"/>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_text_set() : void
    {
        $listener = new ContribLinkListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<contrib xmlns="http://jats.nlm.nih.gov">
    <name>
        <given-names>foo</given-names>
    </name>
</contrib>
XML
        );

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig', ['text' => 'bar']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_text_argument() : void
    {
        $listener = new ContribLinkListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:contrib xmlns:jats="http://jats.nlm.nih.gov">
    <jats:name/>
</jats:contrib>
XML
        );

        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => new TemplateView(
                    '',
                    [
                        'node' => '/jats:contrib/jats:name',
                        'template' => '@LiberoPatterns/link.html.twig',
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
