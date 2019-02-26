<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\EventListener;

use Libero\ContentPageBundle\Event\CreateContentPageEvent;
use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function count;

final class MainListener
{
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onCreatePage(CreateContentPageEvent $event) : void
    {
        $part = new CreateContentPagePartEvent(
            '@LiberoPatterns/page-grid.html.twig',
            $event->getItem(),
            $event->getContext()
        );

        $this->dispatcher->dispatch($part::name('main'), $part);

        if (0 === count($part->getContent())) {
            return;
        }

        $event->addContent($part->getTemplate(), ['isMain' => true] + $part->getContent());
    }
}
