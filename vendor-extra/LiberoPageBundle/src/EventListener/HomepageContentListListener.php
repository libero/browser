<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use FluentDOM;
use FluentDOM\DOM\Document;
use GuzzleHttp\ClientInterface;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\LiberoPageBundle\Event\LoadPageEvent;
use Libero\ViewsBundle\Views\ViewConverter;
use Psr\Http\Message\ResponseInterface;
use function var_dump;

final class HomepageContentListListener
{
    private $client;
    private $converter;

    public function __construct(ClientInterface $client, ViewConverter $converter)
    {
        $this->client = $client;
        $this->converter = $converter;
    }

    public function onLoadPage(LoadPageEvent $event) : void
    {
        $request = $event->getRequest();
        $page = $request->attributes->get('libero_page');

        if ('homepage' !== $page['type']) {
            return;
        }

        $list = $this->client
            ->requestAsync(
                'GET',
                "{$page['content_service']}/items",
                [
                    'headers' => ['Accept' => 'application/xml'],
                    'http_errors' => true,
                ]
            )
            ->then(
                function (ResponseInterface $response) use ($event) : Document {
                    return $this->responseToDocument($response);
                }
            );

        $event->addDocument('content_list', $list);
    }

    public function onCreatePagePart(CreatePagePartEvent $event) : void
    {
        if ('homepage' !== $event->getRequest()->attributes->get('libero_page')['type']) {
            return;
        }

        $list = $event->getDocument('content_list');
        $event->addContent($this->converter->convert($list->documentElement, '@LiberoPatterns/text.html.twig', $event->getContext()));
    }

    public function responseToDocument(ResponseInterface $response) : Document
    {
        return FluentDOM::load((string) $response->getBody());
    }
}
