<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\EventListener;

use Libero\ContentPageBundle\Event\CreateContentPageEvent;
use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\ViewsBundle\Views\View;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function array_merge;
use function count;
use const Libero\LiberoPatternsBundle\CONTENT_GRID_MAIN;
use const Libero\LiberoPatternsBundle\PAGE_GRID_MAIN;

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
            $grid = '@LiberoPatterns/content-grid.html.twig',
            $event->getItem(),
            array_merge($event->getContext(), ['area' => CONTENT_GRID_MAIN])
        );

        $this->dispatcher->dispatch($part::name('main'), $part);

        if (0 === count($part->getContent())) {
            return;
        }

        $event->setContent(
            PAGE_GRID_MAIN,
            new View($part->getTemplate(), ['content' => $part->getContent()], $part->getContext())
        );
    }
}
