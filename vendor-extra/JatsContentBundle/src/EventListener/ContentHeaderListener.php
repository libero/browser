<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\ViewConverter;

final class ContentHeaderListener
{
    private const FRONT_PATH = '/libero:item/jats:article/jats:front';

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onCreatePagePart(CreatePagePartEvent $event) : void
    {
        if ('content' !== $event->getRequest()->attributes->get('libero_page')['type']) {
            return;
        }

        $front = $event->getDocument('content_item')->xpath()->firstOf(self::FRONT_PATH);

        if (!$front instanceof Element) {
            return;
        }

        $context = ['area' => null] + $event->getContext();

        $event->addContent($this->converter->convert($front, '@LiberoPatterns/content-header.html.twig', $context));
    }
}
