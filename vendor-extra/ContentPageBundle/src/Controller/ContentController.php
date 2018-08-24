<?php

namespace Libero\ContentPageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Libero\HttpClientBundle\Clients\FlysystemClient;

final class ContentController
{
    public function __invoke(string $id) : Response
    {
        $client = new FlysystemClient();
        // print_r($client->send());
        return new Response($id, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
