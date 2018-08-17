<?php

namespace Libero\ContentPageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

final class ContentController
{
    public function __invoke(string $id) : Response
    {
        return new Response($id, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
