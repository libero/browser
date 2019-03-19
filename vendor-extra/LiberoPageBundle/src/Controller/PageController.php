<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\Controller;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\Event\LoadPageEvent;
use Libero\LiberoPageBundle\Exception\NoContentSet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use function count;
use function Libero\LiberoPageBundle\text_direction;

final class PageController
{
    private $dispatcher;
    private $template;
    private $twig;

    public function __construct(Environment $twig, string $template, EventDispatcherInterface $dispatcher)
    {
        $this->twig = $twig;
        $this->template = $template;
        $this->dispatcher = $dispatcher;
    }

    public function __invoke(Request $request) : Response
    {
        $context = [
            'lang' => $request->getLocale(),
            'dir' => text_direction($request->getLocale()),
        ];

        $loadEvent = new LoadPageEvent($request, $context);
        $this->dispatcher->dispatch($loadEvent::NAME, $loadEvent);

        $createEvent = new CreatePageEvent($request, $loadEvent->getDocuments()->wait(), $loadEvent->getContext());
        $this->dispatcher->dispatch($createEvent::NAME, $createEvent);

        if (0 === count($createEvent->getContent())) {
            throw NoContentSet::forPage($request->attributes->get('libero_page'));
        }

        return new Response(
            $this->twig->render(
                $this->template,
                $createEvent->getContext() + [
                    'title' => $createEvent->getTitle(),
                    'content' => $createEvent->getContent(),
                ]
            )
        );
    }
}
