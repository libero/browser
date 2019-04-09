<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\EventListener\BuildView;

use Libero\LiberoContentBundle\EventListener\BuildView\FrontTitleTeaserListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontTitleTeaserListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_front_element(string $xml) : void
    {
        $listener = new FrontTitleTeaserListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/teaser.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<front xmlns="http://example.com">foo</front>'];
        yield 'different element' => ['<foo xmlns="http://libero.pub">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_teaser_template() : void
    {
        $listener = new FrontTitleTeaserListener($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://libero.pub"><title>foo</title></front>');

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
    public function it_does_nothing_if_there_is_no_title() : void
    {
        $listener = new FrontTitleTeaserListener($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://libero.pub">foo</front>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/teaser.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_teaser_title_set() : void
    {
        $listener = new FrontTitleTeaserListener($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://libero.pub"><title>foo</title></front>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser.html.twig', ['heading' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertSame(['heading' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_text_argument() : void
    {
        $listener = new FrontTitleTeaserListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<libero:front xmlns:libero="http://libero.pub">
    <libero:title>foo</libero:title>
</libero:front>
XML
        );

        $context = ['bar' => 'baz'];

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser.html.twig', [], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'heading' => [
                    'node' => '/libero:front/libero:title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['bar' => 'baz'],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['bar' => 'baz'], $view->getContext());
    }
}
