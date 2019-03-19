<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function count;

final class ItemTagsListener
{
    use ConvertsLists;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onCreatePage(CreatePageEvent $event) : void
    {
        if ('content' !== $event->getRequest()->attributes->get('libero_page')['type']) {
            return;
        }

        $front = $event->getDocument('content_item')->xpath()->firstOf('/libero:item/jats:article/jats:front');

        if (!$front instanceof Element) {
            return;
        }

        $itemTags = $this->converter->convert($front, '@LiberoPatterns/item-tags.html.twig', $event->getContext());

        if (0 === count($itemTags->getArguments())) {
            return;
        }

        $event->addContent(new View('@LiberoPatterns/single-column-grid.html.twig', ['content' => [$itemTags]]));
    }
}
