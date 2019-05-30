<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM;
use FluentDOM\DOM\Element;
use GuzzleHttp\ClientInterface;
use Libero\ViewsBundle\Views\LazyView;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use Psr\Http\Message\ResponseInterface;

final class ItemRefTeaserListener
{
    use ViewBuildingListener;

    private $client;
    private $converter;

    public function __construct(ClientInterface $client, ViewConverter $converter)
    {
        $this->client = $client;
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        $service = $object->getAttribute('service');
        $id = $object->getAttribute('id');

        if (!$service || !$id) {
            return $view;
        }

        $itemTeaser = $this->client->requestAsync(
            'GET',
            "{$service}/items/{$id}/versions/latest",
            [
                'headers' => ['Accept' => 'application/xml'],
                'http_errors' => true,
            ]
        )
            ->then(
                function (ResponseInterface $response) use ($object, $view) : View {
                    $item = FluentDOM::load((string) $response->getBody());
                    $item->namespaces($object->ownerDocument->namespaces());

                    $itemTeaser = $this->converter->convert(
                        $item->documentElement,
                        $view->getTemplate(),
                        $view->getContext()
                    );

                    if (!$itemTeaser instanceof TemplateView) {
                        return $itemTeaser;
                    }

                    return $view->withArguments($itemTeaser['arguments'])->withContext($itemTeaser->getContext());
                }
            );

        return new LazyView(
            static function () use ($itemTeaser) : TemplateView {
                return $itemTeaser->wait();
            },
            $view->getContext()
        );
    }

    protected function template() : string
    {
        return '@LiberoPatterns/teaser.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://libero.pub}item-ref' === $element->clarkNotation();
    }
}
