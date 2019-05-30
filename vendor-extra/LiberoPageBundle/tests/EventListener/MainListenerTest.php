<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\LiberoPageBundle\EventListener\MainListener;
use Libero\ViewsBundle\Views\TemplateView;
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
            static function (CreatePagePartEvent $event) : void {
                $event->addContent(new TemplateView('template'));
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
     * @dataProvider gridProvider
     */
    public function it_adds_content_to_the_main_area(?string $type, string $grid) : void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            CreatePagePartEvent::name('main'),
            function (CreatePagePartEvent $event) : void {
                $this->assertSame(['con' => 'text', 'area' => 'main'], $event->getContext());

                $event->addContent(new TemplateView('template'));
                $event->setContext('foo', 'bar');
            }
        );

        $mainListener = new MainListener($dispatcher);

        $event = new CreatePageEvent($this->createRequest($type), [], ['con' => 'text']);

        $mainListener->onCreatePage($event);

        $this->assertEquals(
            [
                'main' => new TemplateView(
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
        yield 'homepage' => ['homepage', '@LiberoPatterns/listing-grid.html.twig'];
        yield 'other page' => ['something-else', '@LiberoPatterns/content-grid.html.twig'];
        yield 'unknown page' => [null, '@LiberoPatterns/content-grid.html.twig'];
    }
}
