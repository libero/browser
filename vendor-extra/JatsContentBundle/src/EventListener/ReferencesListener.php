<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\ViewConverter;
use const Libero\LiberoPatternsBundle\CONTENT_GRID_PRIMARY;

final class ReferencesListener
{
    private const REF_LIST_PATH = '/libero:item/jats:article/jats:back/jats:ref-list';

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onCreatePagePart(CreatePagePartEvent $event) : void
    {
        if (!$event->isFor('content')) {
            return;
        }

        $refList = $event->getDocument('content_item')->xpath()->firstOf(self::REF_LIST_PATH);

        if (!$refList instanceof Element) {
            return;
        }

        $context = [
                'area' => CONTENT_GRID_PRIMARY,
                'level' => ($event->getContext()['level'] ?? 1) + 1,
            ] + $event->getContext();

        $event->addContent($this->converter->convert($refList, '@LiberoPatterns/section.html.twig', $context));
    }
}
