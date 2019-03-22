<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\LiberoPageBundle\Event\CreatePageEvent;
use function is_string;

final class TitleListener
{
    private const TITLE_PATH = '/libero:item/libero:front/libero:title';

    public function onCreatePage(CreatePageEvent $event) : void
    {
        if ('content' !== $event->getRequest()->attributes->get('libero_page')['type']) {
            return;
        }

        if (is_string($event->getTitle())) {
            return;
        }

        $title = $event->getDocument('content_item')->xpath()->firstOf(self::TITLE_PATH);

        if (!$title instanceof Element) {
            return;
        }

        $event->setTitle((string) $title);
    }
}
