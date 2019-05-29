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
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class StartListenerTest extends TestCase
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
     * @dataProvider gridProvider
     */
    public function it_adds_content_to_the_start_area(?string $type, string $grid) : void
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

        $event = new CreatePageEvent($this->createRequest($type), [], ['con' => 'text']);

        $startListener->onCreatePage($event);

        $this->assertEquals(
            [
                'start' => new TemplateView(
                    $grid,
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

    public function gridProvider() : iterable
    {
        yield 'content page' => ['content', '@LiberoPatterns/content-grid.html.twig'];
        yield 'homepage' => ['homepage', '@LiberoPatterns/content-grid.html.twig'];
        yield 'other page' => ['something-else', '@LiberoPatterns/content-grid.html.twig'];
        yield 'unknown page' => [null, '@LiberoPatterns/content-grid.html.twig'];
    }
}
