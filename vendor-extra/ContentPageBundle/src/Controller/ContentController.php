<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Controller;

use FluentDOM;
use FluentDOM\DOM\Element;
use GuzzleHttp\ClientInterface;
use Libero\Views\ViewConverter;
use Psr\Http\Message\ResponseInterface;
use Punic\Misc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use UnexpectedValueException;

final class ContentController
{
    private $client;
    private $converter;
    private $service;
    private $twig;

    public function __construct(ClientInterface $client, string $service, Environment $twig, ViewConverter $converter)
    {
        $this->client = $client;
        $this->service = $service;
        $this->twig = $twig;
        $this->converter = $converter;
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
                function (ResponseInterface $response) use ($request) {
                    $dom = FluentDOM::load((string) $response->getBody());
                    $dom->registerNamespace('libero', 'http://libero.pub');

                    $front = $dom('/libero:item/libero:front[1]')->item(0);

                    if (!$front instanceof Element) {
                        throw new UnexpectedValueException('Could not find a front');
                    }

                    $context = [
                        'lang' => $request->getLocale(),
                        'dir' => 'right-to-left' === Misc::getCharacterOrder($request->getLocale()) ? 'rtl' : 'ltr',
                    ];

                    $title = $this->converter->convert($front, '@Patterns/content-header.twig', $context);

                    return new Response(
                        $this->twig->render(
                            'page.html.twig',
                            $context + ['main' => [$title]]
                        )
                    );
                }
            )
            ->wait();
    }
}
