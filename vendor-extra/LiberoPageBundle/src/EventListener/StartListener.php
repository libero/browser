<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use const Libero\LiberoPatternsBundle\MAIN_GRID_MAIN;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function array_merge;
use function count;
use const Libero\LiberoPatternsBundle\PAGE_GRID_START;

final class StartListener
{
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onCreatePage(CreatePageEvent $event) : void
    {
        $part = new CreatePagePartEvent(
          '@LiberoPatterns/content-grid.html.twig',
            $event->getRequest(),
            $event->getDocuments(),
            array_merge($event->getContext(), ['area' => MAIN_GRID_MAIN])
        );

        $this->dispatcher->dispatch($part::name('start'), $part);

        if (0 === count($part->getContent())) {
            return;
        }

        $event->setContent(
          PAGE_GRID_START,
            new TemplateView($part->getTemplate(), ['content' => $part->getContent()], $part->getContext())
        );
    }
}
