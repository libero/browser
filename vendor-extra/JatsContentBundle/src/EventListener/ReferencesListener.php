<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\ViewConverter;
use function count;
use const Libero\LiberoPatternsBundle\CONTENT_GRID_PRIMARY;

final class ReferencesListener
{
    private const REF_LIST_PATH = '/libero:item/jats:article/jats:back/jats:ref-list';

    use ConvertsLists;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onCreatePagePart(CreatePagePartEvent $event) : void
    {
        if (!$event->isFor('content')) {
            return;
        }

        /** @var DOMNodeList<Element> $refLists */
        $refLists = $event->getDocument('content_item')(self::REF_LIST_PATH);

        if (0 === count($refLists)) {
            return;
        }

        $context = [
                'area' => CONTENT_GRID_PRIMARY,
                'level' => ($event->getContext()['level'] ?? 1) + 1,
            ] + $event->getContext();

        $event->addContent(...$this->convertList($refLists, '@LiberoPatterns/section.html.twig', $context));
    }
}
