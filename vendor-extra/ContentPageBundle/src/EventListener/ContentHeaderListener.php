<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\EventListener;

use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function count;

final class ContentHeaderListener
{
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onCreatePagePart(CreateContentPagePartEvent $event) : void
    {
        $part = new CreateContentPagePartEvent(
            '@LiberoPatterns/single-column-grid.html.twig',
            $event->getItem(),
            $event->getContext()
        );

        $this->dispatcher->dispatch($part::name('content-header'), $part);

        if (0 === count($part->getContent())) {
            return;
        }

        $event->addContent('items', ['template' => $part->getTemplate()] + $part->getContent());
    }
}
