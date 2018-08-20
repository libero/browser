<?php

namespace Libero\ContentPageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Libero\ApiClientBundle\ApiClientInterface;
use FluentDOM;

final class ContentController
{
    public function __construct(ApiClientInterface $article)
    {
        $this->article = $article;
    }

    public function __invoke(string $id) : Response
    {
        $xmlString = $this->article->getData();
        $document = FluentDOM::load($xmlString);
        $document->registerNamespace('a', 'http://libero.pub');

        foreach ($document('/a:body/a:section') as $section) {
            $title = $section('string(a:title)');
        }

        return new Response($title, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
