<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\CdataSection;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use FluentDOM\DOM\Text;
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
            if (is_string($template)) {
                throw new LogicException('Expected no template for a non-element node');
            }

            if (!$node instanceof Text && !$node instanceof CdataSection) {
                return new EmptyView($context);
            }

            return new StringView((string) $node, $context);
        }

        $event = new BuildViewEvent($node, new TemplateView($template, [], $context));

        $this->dispatcher->dispatch($event::NAME, $event);

        $view = $event->getView();

        if (!$view->getTemplate()) {
            return new StringView((string) $node, $context);
        }

        return $view;
    }
}
