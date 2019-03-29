<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
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

    public function onCreatePage(CreatePageEvent $event) : void
    {
        $part = new CreatePagePartEvent(
            $grid = '@LiberoPatterns/content-grid.html.twig',
            $event->getRequest(),
            $event->getDocuments(),
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
