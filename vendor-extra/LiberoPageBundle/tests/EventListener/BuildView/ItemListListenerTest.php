<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener\BuildView;

use Libero\LiberoPageBundle\EventListener\BuildView\ItemListListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\LazyView;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ItemListListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_item_list_element(string $xml) : void
    {
        $listener = new ItemListListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/teaser-list.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser-list.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<item-list xmlns="http://example.com"/>'];
        yield 'different element' => ['<not-list xmlns="http://libero.pub"/>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_teaser_list_template() : void
    {
        $listener = new ItemListListener($this->createFailingConverter());

        $element = $this->loadElement('<item-list xmlns="http://libero.pub"/>');

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
    public function it_sets_the_list_items_argument() : void
    {
        $listener = new ItemListListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item-list xmlns:libero="http://libero.pub">
    <libero:item-ref id="id1" service="service1"/>
    <libero:item-ref id="id2" service="service2"/>
</libero:item-list>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser-list.html.twig', [], ['con' => 'text'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(LazyView::class, $view);
        $this->assertSame(
            [
                'list' => [
                    'items' => [
                        [
                            'content' => [
                                'node' => '/libero:item-list/libero:item-ref[1]',
                                'template' => '@LiberoPatterns/teaser.html.twig',
                                'context' => [
                                    'con' => 'text',
                                ],
                            ],
                        ],
                        [
                            'content' => [
                                'node' => '/libero:item-list/libero:item-ref[2]',
                                'template' => '@LiberoPatterns/teaser.html.twig',
                                'context' => [
                                    'con' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $view['arguments']
        );
    }
}
