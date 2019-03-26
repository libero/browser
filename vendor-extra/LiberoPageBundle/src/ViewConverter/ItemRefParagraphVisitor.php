<?php

namespace Libero\LiberoPageBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use GuzzleHttp\ClientInterface;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use Psr\Http\Message\ResponseInterface;

final class ItemRefParagraphVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;

    private $client;
    private $converter;

    public function __construct(ClientInterface $client, ViewConverter $converter)
    {
        $this->client = $client;
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view) : View
    {
        $new = $this->client
            ->requestAsync(
                'GET',
                "{$object->getAttribute('service')}/items/{$object->getAttribute('id')}/versions/latest"
            )
            ->then(
                function (ResponseInterface $response) use ($object, $view) : View {
                    $item = FluentDOM::load((string) $response->getBody());
                    $item->namespaces($object->ownerDocument->namespaces());

                    return $this->converter->convert($item->documentElement, $view->getTemplate(), $view->getContext());
                }
            );

        return View::lazy(
            function () use ($new) {
                return $new->wait();
            }
        );
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/paragraph.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://libero.pub}item-ref'];
    }
}
