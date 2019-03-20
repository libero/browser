<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\EventListener;

use Libero\ContentPageBundle\Event\CreateContentPageEvent;
use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\ContentPageBundle\EventListener\MainListener;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class MainListenerTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_there_are_no_listeners() : void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            CreateContentPagePartEvent::name('not-main'),
            function (CreateContentPagePartEvent $event) : void {
                $event->addContent(new View('template'));
            }
        );

        $mainListener = new MainListener(new EventDispatcher());

        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPageEvent($document);
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
            CreateContentPagePartEvent::name('main'),
            function (CreateContentPagePartEvent $event) : void {
                $this->assertSame(['con' => 'text', 'area' => 'main'], $event->getContext());

                $event->addContent(new View('template'));
                $event->setContext('foo', 'bar');
            }
        );

        $mainListener = new MainListener($dispatcher);

        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPageEvent($document, ['con' => 'text']);

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
