<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Event\CreateContentPageEvent;
use function is_string;

final class TitleListener
{
    public function onCreatePage(CreateContentPageEvent $event) : void
    {
        if (is_string($event->getTitle())) {
            return;
        }

        $title = $event->getItem()->xpath()
            ->firstOf('/libero:item/libero:front/libero:title');

        if (!$title instanceof Element) {
            return;
        }

        $event->setTitle((string) $title);
    }
}
