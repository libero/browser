<?php

namespace Libero\ContentPageBundle\Controller;

use FluentDOM;
use GuzzleHttp\Psr7\Request;
use Libero\ApiClientBundle\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

final class ContentController
{
    private $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    public function __invoke(string $id) : Response
    {
        return $this->client
            ->send(new Request('GET', "{$id}/versions/latest/front"))
            ->then(
                function (string $front) {
                    $front = FluentDOM::load($front);
                    $front->registerNamespace('libero', 'http://libero.pub');

                    $title = $front('string(/libero:front/libero:title)');

                    return new Response($title, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
                }
            )
            ->wait();
    }
}
