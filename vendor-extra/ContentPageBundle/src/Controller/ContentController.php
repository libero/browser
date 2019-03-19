<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Controller;

use FluentDOM;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use GuzzleHttp\ClientInterface;
use Libero\ContentPageBundle\Event\CreateContentPageEvent;
use Libero\ContentPageBundle\Event\CreateHomePageEvent;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use function Libero\ContentPageBundle\text_direction;

final class ContentController
{
    private $client;
    private $dispatcher;
    private $service;
    private $template;
    private $twig;

    public function __construct(
        ClientInterface $client,
        string $service,
        Environment $twig,
        string $template,
        EventDispatcherInterface $dispatcher
    ) {
        $this->client = $client;
        $this->service = $service;
        $this->twig = $twig;
        $this->template = $template;
        $this->dispatcher = $dispatcher;
    }

    public function __invoke(Request $request, ?string $id = null) : Response
    {
        if (!$id) {
            return $this->client
                ->requestAsync(
                    'GET',
                    "{$this->service}/items",
                    [
                        'headers' => ['Accept' => 'application/xml'],
                        'http_errors' => true,
                    ]
                )
                ->then(
                    function (ResponseInterface $response) use ($request) : Response {
                        $document = FluentDOM::load((string) $response->getBody());

                        $context = [
                            'lang' => $request->getLocale(),
                            'dir' => text_direction($request->getLocale()),
                        ];

                        $list = array_map(function (Element $element) use ($request, $context) {
                            return $this->client
                                ->requestAsync(
                                'GET',
                                "{$element->getAttribute('service')}/items/{$element->getAttribute('id')}/versions/latest"
                                )
                                ->then(
                                    function (ResponseInterface $response) use ($request, $context) : Document {
                                        return FluentDOM::load((string) $response->getBody());
                                    }
                                );
                        }, iterator_to_array($document->getElementsByTagName('item-ref')));

                        $event = new CreateHomePageEvent($list, $context);
                        $this->dispatcher->dispatch($event::NAME, $event);

                        return new Response(
                            $this->twig->render(
                                $this->template,
                                $event->getContext() + ['title' => 'Home', 'content' => $event->getContent()]
                            )
                        );
                    }
                )
                ->wait();
        } else {
            return $this->client
                ->requestAsync(
                    'GET',
                    "{$this->service}/items/{$id}/versions/latest",
                    [
                        'headers' => ['Accept' => 'application/xml'],
                        'http_errors' => true,
                    ]
                )
                ->then(
                    function (ResponseInterface $response) use ($request) : Response {
                        $document = FluentDOM::load((string) $response->getBody());

                        $context = [
                            'lang' => $request->getLocale(),
                            'dir' => text_direction($request->getLocale()),
                        ];

                        $event = new CreateContentPageEvent($document, $context);
                        $this->dispatcher->dispatch($event::NAME, $event);

                        return new Response(
                            $this->twig->render(
                                $this->template,
                                $event->getContext() + ['title' => $event->getTitle(), 'content' => $event->getContent()]
                            )
                        );
                    }
                )
                ->wait();
        }
    }
}
