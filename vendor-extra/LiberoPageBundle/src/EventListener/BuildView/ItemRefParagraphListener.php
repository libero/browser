<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM;
use GuzzleHttp\ClientInterface;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Psr\Http\Message\ResponseInterface;
use function sprintf;

final class ItemRefParagraphListener
{
    private $client;
    private $converter;

    public function __construct(ClientInterface $client, ViewConverter $converter)
    {
        $this->client = $client;
        $this->converter = $converter;
    }

    public function onBuildView(BuildViewEvent $event) : void
    {
        $object = $event->getObject();
        $view = $event->getView();

        if (!$this->canHandleTemplate($view->getTemplate())) {
            return;
        }

        if (!$this->canHandleElement(sprintf('{%s}%s', $object->namespaceURI, $object->localName))) {
            return;
        }

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

        $view = View::lazy([$new, 'wait']);

        $event->setView($view);
        $event->stopPropagation();
    }

    protected function canHandleTemplate(string $template) : bool
    {
        return '@LiberoPatterns/paragraph.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://libero.pub}item-ref' === $element;
    }
}
