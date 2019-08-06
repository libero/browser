<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\ViewConverter;
use function count;

final class ContextualDataListener
{
    private const FRONT_PATH = '/libero:item/jats:article/jats:front';

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

        $front = $event->getDocument('content_item')->xpath()->firstOf(self::FRONT_PATH);

        if (!$front instanceof Element) {
            return;
        }

        $context = ['area' => null] + $event->getContext();

        $contextualData = $this->converter->convert($front, '@LiberoPatterns/contextual-data.html.twig', $context);

        if ($contextualData instanceof TemplateView && 0 === count($contextualData->getArguments())) {
            return;
        }

        $event->addContent($contextualData);
    }
}
