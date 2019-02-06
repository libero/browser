<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Controller;

use FluentDOM;
use GuzzleHttp\ClientInterface;
use Libero\ContentPageBundle\Handler\ContentHandler;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use function Libero\ContentPageBundle\text_direction;

final class ContentController
{
    private $client;
    private $contentHandler;
    private $service;
    private $template;
    private $twig;

    public function __construct(
        ClientInterface $client,
        string $service,
        Environment $twig,
        string $template,
        ContentHandler $contentHandler
    ) {
        $this->client = $client;
        $this->service = $service;
        $this->twig = $twig;
        $this->template = $template;
        $this->contentHandler = $contentHandler;
    }

    public function __invoke(Request $request, string $id) : Response
    {
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
                    $dom = FluentDOM::load((string) $response->getBody());

                    $context = [
                        'lang' => $request->getLocale(),
                        'dir' => text_direction($request->getLocale()),
                    ];

                    return new Response(
                        $this->twig->render(
                            $this->template,
                            $this->contentHandler->handle($dom->documentElement, $context)
                        )
                    );
                }
            )
            ->wait();
    }
}
