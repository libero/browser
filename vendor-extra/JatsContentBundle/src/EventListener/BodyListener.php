<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Event\CreateContentPageEvent;
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

    public function onCreatePage(CreateContentPageEvent $event) : void
    {
        $xpath = $event->getItem()->xpath();

        $body = $xpath->firstOf('/libero:item/jats:article/jats:body');

        if (!$body instanceof Element) {
            return;
        }

        $event->addContent(
            new View(
                '@LiberoPatterns/single-column-grid.html.twig',
                ['content' => $this->convertChildren($body, $event->getContext())]
            )
        );
    }
}
