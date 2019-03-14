<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

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
            ->firstOf('/libero:item/jats:article/jats:front/jats:article-meta/jats:title-group/jats:article-title');

        if (!$title instanceof Element) {
            return;
        }

        $event->setTitle((string) $title);
    }
}
