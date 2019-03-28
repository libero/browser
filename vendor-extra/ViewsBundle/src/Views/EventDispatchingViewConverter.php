<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Event\BuildViewEvent;
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function is_string;

final class EventDispatchingViewConverter implements ViewConverter
{
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function convert(NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : View
    {
        if (!$node instanceof Element) {
            if (is_string($template) && '@LiberoPatterns/text.html.twig' !== $template) {
                throw new LogicException(
                    "Expected the template '@LiberoPatterns/text.html.twig' for a non-element node"
                );
            }

            return new View('@LiberoPatterns/text.html.twig', ['nodes' => (string) $node], $context);
        }

        $event = new BuildViewEvent($node, new View($template, [], $context));

        $this->dispatcher->dispatch($event::NAME, $event);

        $view = $event->getView();

        if (!$view->getTemplate()) {
            return new View('@LiberoPatterns/text.html.twig', ['nodes' => (string) $node], $context);
        }

        return $view;
    }
}
