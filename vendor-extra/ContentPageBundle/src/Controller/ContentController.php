<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Controller;

use DOMNodeList;
use FluentDOM;
use FluentDOM\DOM\Element;
use GuzzleHttp\ClientInterface;
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
    private $service;
    private $template;
    private $twig;

    public function __construct(
        ClientInterface $client,
        string $service,
        Environment $twig,
        string $template
    ) {
        $this->client = $client;
        $this->service = $service;
        $this->twig = $twig;
        $this->template = $template;
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
                    $dom->registerNamespace('libero', 'http://libero.pub');

                    /** @var DOMNodeList|Element[] $frontList */
                    $frontList = $dom('/libero:item/libero:front[1]');
                    $front = $frontList->item(0);

                    if (!$front instanceof Element) {
                        throw new UnexpectedValueException('Could not find a front');
                    }

                    /** @var string $title */
                    $title = $front('string(libero:title)');

                    if ('' === $title) {
                        throw new UnexpectedValueException('Could not find a title');
                    }

                    $context = [
                        'lang' => $request->getLocale(),
                        'dir' => $this->getDirection($request->getLocale()),
                    ];

                    $contentHeader = [
                        'template' => '@LiberoPatterns/content-header.html.twig',
                        'arguments' => [
                            'attributes' => [],
                            'contentTitle' => [
                                'text' => $title,
                            ],
                        ],
                    ];

                    if ($context['lang'] !== $frontLang = $front->getAttribute('xml:lang')) {
                        $contentHeader['arguments']['attributes']['lang'] = $frontLang;

                        if ($context['dir'] !== $frontDir = $this->getDirection($frontLang)) {
                            $contentHeader['arguments']['attributes']['dir'] = $frontDir;
                        }
                    }

                    return new Response(
                        $this->twig->render(
                            $this->template,
                            array_merge(
                                $context,
                                [
                                    'title' => $title,
                                    'content' => [$contentHeader],
                                ]
                            )
                        )
                    );
                }
            )
            ->wait();
    }

    private function getDirection(?string $locale) : string
    {
        return 'right-to-left' === Misc::getCharacterOrder($locale ?? 'en') ? 'rtl' : 'ltr';
    }
}
