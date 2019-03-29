<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use FluentDOM;
use FluentDOM\DOM\Document;
use GuzzleHttp\ClientInterface;
use Libero\LiberoPageBundle\Event\LoadPageDataEvent;
use Psr\Http\Message\ResponseInterface;

final class ContentItemListener
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function onLoadPageData(LoadPageDataEvent $event) : void
    {
        if (!$event->isFor('content')) {
            return;
        }

        $page = $event->getRequest()->attributes->get('libero_page');

        $event->addDocument(
            'content_item',
            $this->client->requestAsync(
                'GET',
                "{$page['content_service']}/items/{$page['content_id']}/versions/latest",
                [
                    'headers' => ['Accept' => 'application/xml'],
                    'http_errors' => true,
                ]
            )
                ->then(
                    function (ResponseInterface $response) : Document {
                        return FluentDOM::load((string) $response->getBody());
                    }
                )
        );
    }
}
