<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\ViewConverter;
use function count;
use const Libero\LiberoPatternsBundle\CONTENT_GRID_PRIMARY;

final class ItemTagsListener
{
    use ConvertsLists;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onCreatePageMain(CreateContentPagePartEvent $event) : void
    {
        $front = $event->getItem()->xpath()->firstOf('/libero:item/jats:article/jats:front');

        if (!$front instanceof Element) {
            return;
        }

        $context = ['area' => CONTENT_GRID_PRIMARY] + $event->getContext();

        $itemTags = $this->converter->convert($front, '@LiberoPatterns/item-tags.html.twig', $context);

        if (0 === count($itemTags->getArguments())) {
            return;
        }

        $event->addContent($itemTags);
    }
}
