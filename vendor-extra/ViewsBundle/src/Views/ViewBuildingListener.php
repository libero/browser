<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Event\BuildViewEvent;

trait ViewBuildingListener
{
    use SimplifiedViewConverterListener;

    final public function onBuildView(BuildViewEvent $event) : void
    {
        $object = $event->getObject();
        $view = $event->getView();

        if (!$view instanceof TemplateView || $view->getTemplate() !== $this->template()) {
            return;
        }

        if (!$this->canHandleElement($object)) {
            return;
        }

        if (!$this->canHandleArguments($view->getArguments())) {
            return;
        }

        $event->setView($this->handle($object, $view));
    }

    abstract protected function handle(Element $object, TemplateView $view) : View;
}
