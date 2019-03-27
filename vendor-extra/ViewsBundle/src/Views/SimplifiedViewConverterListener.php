<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Event\CreateViewEvent;

trait SimplifiedViewConverterListener
{
    use OptionalViewConverterListener;

    final public function onCreateView(CreateViewEvent $event) : void
    {
        if (!$this->check($event)) {
            return;
        }

        $event->setView($this->handle($event->getObject(), $event->getView()));
    }

    abstract protected function handle(Element $object, View $view) : View;
}
