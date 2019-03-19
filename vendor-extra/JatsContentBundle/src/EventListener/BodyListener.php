<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;

final class BodyListener
{
    use ConvertsChildren;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onCreatePage(CreatePageEvent $event) : void
    {
        if ('content' !== $event->getRequest()->attributes->get('libero_page')['type']) {
            return;
        }

        $xpath = $event->getDocument('content_item')->xpath();

        $body = $xpath->firstOf('/libero:item/jats:article/jats:body');

        if (!$body instanceof Element) {
            return;
        }

        $context = ['level' => ($event->getContext()['level'] ?? 1) + 1] + $event->getContext();

        $event->addContent(
            new View(
                '@LiberoPatterns/single-column-grid.html.twig',
                ['content' => $this->convertChildren($body, $context)]
            )
        );
    }
}
