<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Event\BuildViewEvent;
use function sprintf;

trait SimplifiedViewConverterListener
{
    final public function onBuildView(BuildViewEvent $event) : void
    {
        $object = $event->getObject();
        $view = $event->getView();

        if (!$view instanceof TemplateView || !$this->canHandleTemplate($view->getTemplate())) {
            return;
        }

        if (!$this->canHandleElement(sprintf('{%s}%s', $object->namespaceURI, $object->localName))) {
            return;
        }

        if (!$this->canHandleArguments($view->getArguments())) {
            return;
        }

        $view = $this->beforeHandle($view);

        $event->setView($this->handle($object, $view));
    }

    abstract protected function handle(Element $object, TemplateView $view) : View;

    abstract protected function canHandleTemplate(?string $template) : bool;

    abstract protected function canHandleElement(string $element) : bool;

    protected function canHandleArguments(array $arguments) : bool
    {
        return true;
    }

    protected function beforeHandle(TemplateView $view) : TemplateView
    {
        return $view;
    }
}
