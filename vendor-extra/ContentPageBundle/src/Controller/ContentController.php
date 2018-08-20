<?php

namespace Libero\ContentPageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Libero\ApiClientBundle\ApiClientInterface;

final class ContentController
{
    public function __construct(ApiClientInterface $article)
    {
        $this->article = $article;
    }

    public function __invoke(string $id) : Response
    {
        return new Response($id, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
