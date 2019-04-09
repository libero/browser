<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\EventListener\BuildView;

use Libero\LiberoContentBundle\EventListener\BuildView\ItemTeaserListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ItemTeaserListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_item_element(string $xml) : void
    {
        $listener = new ItemTeaserListener($this->createFailingConverter());

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
        yield 'different namespace' => ['<item xmlns="http://example.com">foo</item>'];
        yield 'different element' => ['<foo xmlns="http://libero.pub">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_teaser_template() : void
    {
        $listener = new ItemTeaserListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <meta>
        <id>article1</id>
        <service>scholarly-articles</service>
    </meta>
    <front>
        <title>foo</title>
    </front>
</item>
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
    public function it_does_nothing_if_there_is_no_libero_front() : void
    {
        $listener = new ItemTeaserListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <meta>
        <id>article1</id>
        <service>scholarly-articles</service>
    </meta>
</item>
XML
        );

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
    public function it_converts_a_libero_front_into_a_teaser() : void
    {
        $listener = new ItemTeaserListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>article1</libero:id>
        <libero:service>scholarly-articles</libero:service>
    </libero:meta>
    <libero:front>
        <libero:title>foo</libero:title>
    </libero:front>
</libero:item>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser.html.twig', [], ['con' => 'text'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame(
            [
                'node' => '/libero:item/libero:front',
                'template' => '@LiberoPatterns/teaser.html.twig',
                'context' => ['con' => 'text'],
            ],
            $view->getArguments()
        );
        $this->assertTrue($event->isPropagationStopped());
    }
}
