<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\LiberoPageBundle\EventListener\MainListener;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class MainListenerTest extends TestCase
{
    use PageTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_there_are_no_listeners() : void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            CreatePagePartEvent::name('not-main'),
            function (CreatePagePartEvent $event) : void {
                $event->addContent(new View('template'));
            }
        );

        $mainListener = new MainListener(new EventDispatcher());

        $event = new CreatePageEvent($this->createRequest('page'));
        $expected = clone $event;

        $mainListener->onCreatePage($event);

        $this->assertEquals($expected, $event);
    }

    /**
     * @test
     */
    public function it_adds_content_to_the_main_area() : void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            CreatePagePartEvent::name('main'),
            function (CreatePagePartEvent $event) : void {
                $this->assertSame(['con' => 'text', 'area' => 'main'], $event->getContext());

                $event->addContent(new View('template'));
                $event->setContext('foo', 'bar');
            }
        );

        $mainListener = new MainListener($dispatcher);

        $event = new CreatePageEvent($this->createRequest('page'), [], ['con' => 'text']);

        $mainListener->onCreatePage($event);

        $this->assertEquals(
            [
                'main' => new View(
                    '@LiberoPatterns/content-grid.html.twig',
                    [
                        'content' => [
                            new View('template'),
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
