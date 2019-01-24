<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Controller;

use FluentDOM;
use FluentDOM\DOM\Element;
use GuzzleHttp\ClientInterface;
use Libero\ViewsBundle\Views\ViewConverter;
use Psr\Http\Message\ResponseInterface;
use Punic\Misc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use UnexpectedValueException;
use function array_merge;

final class ContentController
{
    private $client;
    private $converter;
    private $service;
    private $template;
    private $twig;

    public function __construct(
        ClientInterface $client,
        string $service,
        Environment $twig,
        string $template,
        ViewConverter $converter
    ) {
        $this->client = $client;
        $this->service = $service;
        $this->twig = $twig;
        $this->template = $template;
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
                function (ResponseInterface $response) use ($request) : Response {
                    $dom = FluentDOM::load((string) $response->getBody());
                    $xpath = $dom->xpath();
                    $xpath->registerNamespace('libero', 'http://libero.pub');

                    $front = $xpath->firstOf('/libero:item/libero:front[1]');

                    if (!$front instanceof Element) {
                        throw new UnexpectedValueException('Could not find a front');
                    }

                    $context = [
                        'lang' => $request->getLocale(),
                        'dir' => $this->getDirection($request->getLocale()),
                    ];

                    $header = $this->converter->convert($front, '@LiberoPatterns/content-header.html.twig', $context);

                    return new Response(
                        $this->twig->render(
                            $this->template,
                            array_merge(
                                $context,
                                [
                                    'title' => $xpath('string(libero:title[1])', $front),
                                    'content' => [$header],
                                ]
                            )
                        )
                    );
                }
            )
            ->wait();
    }

    private function getDirection(string $locale) : string
    {
        return 'right-to-left' === Misc::getCharacterOrder($locale) ? 'rtl' : 'ltr';
    }
}
