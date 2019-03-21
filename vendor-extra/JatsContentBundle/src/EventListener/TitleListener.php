<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Event\CreateContentPageEvent;
use function is_string;

final class TitleListener
{
    private const FRONT_PATH = '/libero:item/jats:article/jats:front';
    private const TITLE_PATH = self::FRONT_PATH.'/jats:article-meta/jats:title-group/jats:article-title';

    public function onCreatePage(CreateContentPageEvent $event) : void
    {
        if (is_string($event->getTitle())) {
            return;
        }

        $title = $event->getItem()->xpath()->firstOf(self::TITLE_PATH);

        if (!$title instanceof Element) {
            return;
        }

        $event->setTitle((string) $title);
    }
}
