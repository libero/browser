<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\ViewConverter;
use const Libero\LiberoPatternsBundle\CONTENT_GRID_PRIMARY;

final class BodyListener
{
    use ConvertsChildren;

    private const BODY_PATH = '/libero:item/jats:article/jats:body';

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

        $body = $event->getDocument('content_item')->xpath()->firstOf(self::BODY_PATH);

        if (!$body instanceof Element) {
            return;
        }

        $context = [
                'area' => CONTENT_GRID_PRIMARY,
                'level' => ($event->getContext()['level'] ?? 1) + 1,
            ] + $event->getContext();

        $event->addContent(...$this->convertChildren($body, $context));
    }
}
