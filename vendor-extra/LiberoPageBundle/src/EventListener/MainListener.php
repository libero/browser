<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function array_merge;
use function count;
use const Libero\LiberoPatternsBundle\MAIN_GRID_MAIN;
use const Libero\LiberoPatternsBundle\PAGE_GRID_MAIN;

final class MainListener
{
    private const PAGE_GRID_PART = PAGE_GRID_MAIN;

    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onCreatePage(CreatePageEvent $event) : void
    {
        switch ($event->getRequest()->attributes->get('libero_page')['type'] ?? null) {
            case 'homepage':
                $grid = '@LiberoPatterns/listing-grid.html.twig';
                break;
            default:
                $grid = '@LiberoPatterns/content-grid.html.twig';
                break;
        }

        $part = new CreatePagePartEvent(
            $grid,
            $event->getRequest(),
            $event->getDocuments(),
            array_merge($event->getContext(), ['area' => MAIN_GRID_MAIN])
        );

        $this->dispatcher->dispatch($part::name(self::PAGE_GRID_PART), $part);

        if (0 === count($part->getContent())) {
            return;
        }

        $event->setContent(
            self::PAGE_GRID_PART,
            new TemplateView($part->getTemplate(), ['content' => $part->getContent()], $part->getContext())
        );
    }
}
