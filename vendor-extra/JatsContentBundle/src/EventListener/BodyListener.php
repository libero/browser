<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\ViewConverter;

final class BodyListener
{
    use ConvertsChildren;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onCreatePageMain(CreateContentPagePartEvent $event) : void
    {
        $body = $event->getItem()->xpath()
            ->firstOf('/libero:item/jats:article/jats:body');

        if (!$body instanceof Element) {
            return;
        }

        $context = ['area' => 'primary', 'level' => ($event->getContext()['level'] ?? 1) + 1] + $event->getContext();

        $event->addContent(...$this->convertChildren($body, $context));
    }
}
