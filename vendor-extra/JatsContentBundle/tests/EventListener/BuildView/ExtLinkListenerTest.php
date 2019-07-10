<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\ExtLinkListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Event\ChooseTemplateEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ExtLinkListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider templateChoiceProvider
     */
    public function it_can_choose_a_template(string $xml, ?string $expected) : void
    {
        $listener = new ExtLinkListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new ChooseTemplateEvent($element);
        $listener->onChooseTemplate($event);

        $this->assertSame($expected, $event->getTemplate());
    }

    public function templateChoiceProvider() : iterable
    {
        yield 'ext-link element' => [
            <<<XML
<ext-link xlink:href="bar" xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink">
    foo
</ext-link>
XML
            ,
            '@LiberoPatterns/link.html.twig',
        ];
        yield 'ext-link element no href' => ['<ext-link xmlns="http://jats.nlm.nih.gov">foo</ext-link>', null];
        yield 'different namespace' => ['<ext-link xmlns="http://example.com">foo</ext-link>', null];
        yield 'different element' => ['<italic xmlns="http://jats.nlm.nih.gov">foo</italic>', null];
    }

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_ext_link_element_with_an_absolute_href(string $xml) : void
    {
        $listener = new ExtLinkListener($this->createFailingConverter());

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
        yield 'no href' => ['<ext-link xmlns="http://jats.nlm.nih.gov">foo</ext-link>'];
        yield 'relative link' => [
            <<<XML
<ext-link xlink:href="bar" xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink">
    foo
</ext-link>
XML
            ,
        ];
        yield 'different namespace' => ['<ext-link xmlns="http://example.com">foo</ext-link>'];
        yield 'different element' => ['<italic xmlns="http://jats.nlm.nih.gov">foo</italic>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_link_template() : void
    {
        $listener = new ExtLinkListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<ext-link xlink:href="bar" xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink">
    foo
</ext-link>
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
    public function it_does_nothing_if_there_is_already_text_set() : void
    {
        $listener = new ExtLinkListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<ext-link xlink:href="bar" xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink">
    foo
</ext-link>
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
    public function it_sets_the_text_argument_and_href_attribute() : void
    {
        $listener = new ExtLinkListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:ext-link xlink:href="http://example.com" xmlns:jats="http://jats.nlm.nih.gov"
    xmlns:xlink="http://www.w3.org/1999/xlink">
    foo <jats:italic>bar</jats:italic> baz
</jats:ext-link>
XML
        );

        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/link.html.twig', ['attributes' => ['att' => 'ribute']], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'attributes' => [
                    'att' => 'ribute',
                    'href' => 'http://example.com',
                ],
                'text' => [
                    new TemplateView(
                        '',
                        ['node' => '/jats:ext-link/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new TemplateView(
                        '',
                        ['node' => '/jats:ext-link/jats:italic', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new TemplateView(
                        '',
                        ['node' => '/jats:ext-link/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
