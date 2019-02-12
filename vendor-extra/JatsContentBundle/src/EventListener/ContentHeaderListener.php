<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Event\CreateContentPageEvent;
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
        $xpath = $event->getItem()->xpath();

        $front = $xpath->firstOf('/libero:item/jats:article/jats:front');

        if (!$front instanceof Element) {
            return;
        }

        $title = $xpath->firstOf('jats:article-meta/jats:title-group/jats:article-title', $front);

        if ($title instanceof Element && !is_string($event->getTitle())) {
            $event->setTitle((string) $title);
        }

        $event->addContent(
            $this->converter->convert($front, '@LiberoPatterns/content-header.html.twig', $event->getContext())
        );
    }
}
