<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Controller;

use FluentDOM;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

final class ContentController
{
    private $client;
    private $service;

    public function __construct(ClientInterface $client, string $service)
    {
        $this->client = $client;
        $this->service = $service;
    }

    public function __invoke(string $id) : Response
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
                function (ResponseInterface $response) {
                    $dom = FluentDOM::load((string) $response->getBody());
                    $dom->registerNamespace('libero', 'http://libero.pub');

                    /** @var string $title */
                    $title = $dom('string(/libero:item/libero:front/libero:title)');

                    if ('' === $title) {
                        throw new UnexpectedValueException('Could not find a title');
                    }

                    return new Response("<html><body>${title}</body></html>");
                }
            )
            ->wait();
    }
}
