<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\ViewsBundle\Views\ViewConverter;

final class ContentHeaderListener
{
    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onCreatePageMain(CreateContentPagePartEvent $event) : void
    {
        $front = $event->getItem()->xpath()
            ->firstOf('/libero:item/jats:article/jats:front');

        if (!$front instanceof Element) {
            return;
        }

        $context = ['area' => null] + $event->getContext();

        $event->addContent($this->converter->convert($front, '@LiberoPatterns/content-header.html.twig', $context));
    }
}
