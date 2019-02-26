<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\ViewConverter;
use function count;

final class ItemTagsListener
{
    use ConvertsLists;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onCreatePageBody(CreateContentPagePartEvent $event) : void
    {
        $front = $event->getItem()->xpath()->firstOf('/libero:item/jats:article/jats:front');

        if (!$front instanceof Element) {
            return;
        }

        $itemTags = $this->converter->convert($front, '@LiberoPatterns/item-tags.html.twig', $event->getContext());

        if (0 === count($itemTags->getArguments())) {
            return;
        }

        $event->addContent('primaryContent', $itemTags);
    }
}
