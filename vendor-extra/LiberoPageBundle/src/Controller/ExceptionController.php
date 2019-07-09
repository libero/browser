<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\Controller;

use Libero\LiberoPageBundle\Event\BuildErrorEvent;
use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\Exception\NoContentSet;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Twig\Environment;
use function count;
use function Libero\LiberoPageBundle\text_direction;
use const Libero\LiberoPatternsBundle\PAGE_GRID_MAIN;

final class ExceptionController extends BaseExceptionController
{
    private $dispatcher;
    private $template;

    public function __construct(
        Environment $twig,
        string $template,
        EventDispatcherInterface $dispatcher,
        bool $debug
    ) {
        parent::__construct($twig, $debug);

        $this->template = $template;
        $this->dispatcher = $dispatcher;
    }

    public function showAction(
        Request $request,
        FlattenException $exception,
        ?DebugLoggerInterface $logger = null
    ) : Response {
        if ($request->attributes->get('showException', $this->debug)) {
            return parent::showAction($request, $exception, $logger);
        }

        $this->getAndCleanOutputBuffering((int) $request->headers->get('X-Php-Ob-Level', '-1'));

        $context = [
            'lang' => $request->getLocale(),
            'dir' => text_direction($request->getLocale()),
        ];

        $request->attributes->set('libero_page', ['type' => 'error']);

        $createEvent = new CreatePageEvent($request, [], $context);
        $this->dispatcher->dispatch($createEvent::NAME, $createEvent);

        if (0 === count($createEvent->getContent())) {
            throw NoContentSet::forPage($request->attributes->get('libero_page'));
        }

        $errorEvent = new BuildErrorEvent(
            $exception,
            new TemplateView('@LiberoPatterns/error.html.twig', [], $createEvent->getContext())
        );

        $this->dispatcher->dispatch($errorEvent::NAME, $errorEvent);

        $createEvent->setContent(PAGE_GRID_MAIN, $errorEvent->getView());

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
