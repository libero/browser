<?php

namespace Libero\ContentPageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Libero\HttpClientBundle\HttpClientInterface;

final class ContentController
{
    public function __construct (HttpClientInterface $client) {
        $this->client = $client;
    }

    public function __invoke(string $id) : Response
    {
        $promise = $this->client->send()->then(function ($result) {
            return $result;
        });

        $response = $promise->wait();

        return new Response($id, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
