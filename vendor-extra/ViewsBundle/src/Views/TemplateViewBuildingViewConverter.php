<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function is_string;

final class TemplateViewBuildingViewConverter implements ViewConverter
{
    private $dispatcher;
    private $fallback;

    public function __construct(EventDispatcherInterface $dispatcher, ViewConverter $fallback)
    {
        $this->dispatcher = $dispatcher;
        $this->fallback = $fallback;
    }

    public function convert(NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : View
    {
        if (!$node instanceof Element || !is_string($template)) {
            return $this->fallback->convert($node, $template, $context);
        }

        $event = new BuildViewEvent($node, new TemplateView($template, [], $context));

        $this->dispatcher->dispatch($event::NAME, $event);

        return $event->getView();
    }
}
