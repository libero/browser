<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM;
use FluentDOM\DOM\Element;
use GuzzleHttp\ClientInterface;
use Libero\ViewsBundle\Views\LazyView;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Psr\Http\Message\ResponseInterface;

final class ItemRefTeaserListener
{
    use SimplifiedViewConverterListener;

    private $client;
    private $converter;

    public function __construct(ClientInterface $client, ViewConverter $converter)
    {
        $this->client = $client;
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        $itemTeaser = $this->client->requestAsync(
            'GET',
            "{$object->getAttribute('service')}/items/{$object->getAttribute('id')}/versions/latest",
            [
                'headers' => ['Accept' => 'application/xml'],
                'http_errors' => true,
            ]
        )
            ->then(
                function (ResponseInterface $response) use ($object, $view) : View {
                    $item = FluentDOM::load((string) $response->getBody());
                    $item->namespaces($object->ownerDocument->namespaces());

                    return $this->converter->convert($item->documentElement, $view->getTemplate(), $view->getContext());
                }
            );

        return new LazyView(
            function () use ($itemTeaser) : TemplateView {
                return $itemTeaser->wait();
            },
            $view->getContext()
        );
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/teaser.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://libero.pub}item-ref' === $element;
    }
}
