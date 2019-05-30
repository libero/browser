<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\LiberoPageBundle\EventListener\StartListener;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use tests\Libero\LiberoPageBundle\PageTestCase;

final class StartListenerTest extends TestCase
{
    use PageTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_there_are_no_listeners() : void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            CreatePagePartEvent::name('not-start'),
            function (CreatePagePartEvent $event) : void {
                $event->addContent(new TemplateView('template'));
            }
        );

        $startListener = new StartListener(new EventDispatcher());

        $event = new CreatePageEvent($this->createRequest('page'));
        $expected = clone $event;

        $startListener->onCreatePage($event);

        $this->assertEquals($expected, $event);
    }

    /**
     * @test
     */
    public function it_adds_content_to_the_start_area() : void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            CreatePagePartEvent::name('start'),
            function (CreatePagePartEvent $event) : void {
                $this->assertSame(['con' => 'text', 'area' => 'main'], $event->getContext());

                $event->addContent(new TemplateView('template'));
                $event->setContext('foo', 'bar');
            }
        );

        $startListener = new StartListener($dispatcher);

        $event = new CreatePageEvent($this->createRequest(null), [], ['con' => 'text']);

        $startListener->onCreatePage($event);

        $this->assertEquals(
            [
                'start' => new TemplateView(
                    '@LiberoPatterns/content-grid.html.twig',
                    [
                        'content' => [
                            new TemplateView('template'),
                        ],
                    ],
                    [
                        'con' => 'text',
                        'area' => 'main',
                        'foo' => 'bar',
                    ]
                ),
            ],
            $event->getContent()
        );
        $this->assertSame(['con' => 'text'], $event->getContext());
        $this->assertNull($event->getTitle());
    }
}
