<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\Controller;

use Libero\LiberoPageBundle\Controller\PageController;
use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\Event\LoadPageDataEvent;
use Libero\LiberoPageBundle\Exception\NoContentSet;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\TwigTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;
use function GuzzleHttp\Promise\promise_for;

final class PageControllerTest extends TestCase
{
    use PageTestCase;
    use TwigTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider pageProvider
     */
    public function it_returns_the_title(Request $request, array $twigContext) : void
    {
        $listeners = [
            LoadPageDataEvent::NAME => function (LoadPageDataEvent $event) : void {
                $document = $this->loadDocument('<foo><bar>content</bar></foo>');
                $event->addDocument('foo', promise_for($document));
            },
            CreatePageEvent::NAME => function (CreatePageEvent $event) : void {
                $event->setContext('context', $event->getContext());
                $event->setTitle('title');
                $event->setContent(
                    'area',
                    new View(
                        'template',
                        [
                        'content' => $event->getDocument('foo')
                            ->textContent,
                        ]
                    )
                );
            },
        ];

        $controller = $this->createPageController($listeners);

        $response = $controller($request);
        $response->prepare($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertTwigRender(['template.html.twig', $twigContext], $response->getContent());
    }

    public function pageProvider() : iterable
    {
        yield 'en request' => [
            $this->createRequest('type'),
            [
                'lang' => 'en',
                'dir' => 'ltr',
                'context' => [
                    'lang' => 'en',
                    'dir' => 'ltr',
                ],
                'title' => 'title',
                'content' => [
                    'area' => [
                        'template' => 'template',
                        'arguments' => [
                            'content' => 'content',
                        ],
                    ],
                ],
            ],
        ];

        $frenchRequest = $this->createRequest('type');
        $frenchRequest->setLocale('fr');

        yield 'fr request' => [
            $frenchRequest,
            [
                'lang' => 'fr',
                'dir' => 'ltr',
                'context' => [
                    'lang' => 'fr',
                    'dir' => 'ltr',
                ],
                'title' => 'title',
                'content' => [
                    'area' => [
                        'template' => 'template',
                        'arguments' => [
                            'content' => 'content',
                        ],
                    ],
                ],
            ],
        ];

        $arabicRequest = $this->createRequest('type');
        $arabicRequest->setLocale('ar-EG');

        yield 'ar-EG request' => [
            $arabicRequest,
            [
                'lang' => 'ar-EG',
                'dir' => 'rtl',
                'context' => [
                    'lang' => 'ar-EG',
                    'dir' => 'rtl',
                ],
                'title' => 'title',
                'content' => [
                    'area' => [
                        'template' => 'template',
                        'arguments' => [
                            'content' => 'content',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_no_content_is_produced() : void
    {
        $controller = $this->createPageController();

        $this->expectException(NoContentSet::class);
        $this->expectExceptionMessage("No content has been added to type page 'name'");

        $controller($this->createRequest('type', 'name'));
    }

    private function createPageController(
        array $listeners = [],
        string $template = 'template.html.twig'
    ) : PageController {
        $dispatcher = new EventDispatcher();
        foreach ($listeners as $eventName => $listener) {
            $dispatcher->addListener($eventName, $listener);
        }

        return new PageController($this->createTwig(), $template, $dispatcher);
    }
}
