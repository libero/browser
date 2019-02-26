<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Event\CreateContentPageEvent;
use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\ViewsBundle\Views\ViewConverter;
use function is_string;

final class ContentHeaderListener
{
    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

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

    public function onCreatePageContentHeader(CreateContentPagePartEvent $event) : void
    {
        $front = $event->getItem()->xpath()
            ->firstOf('/libero:item/jats:article/jats:front');

        if (!$front instanceof Element) {
            return;
        }

        $event->addContent(
            'content',
            $this->converter->convert($front, '@LiberoPatterns/content-header.html.twig', $event->getContext())
        );
    }
}
