<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Event\ChooseTemplateEvent;

trait TemplateChoosingListener
{
    use SimplifiedViewConverterListener;

    final public function onChooseTemplate(ChooseTemplateEvent $event) : void
    {
        $object = $event->getObject();

        if (!$this->canHandleElement($object)) {
            return;
        }

        $event->setTemplate($this->template());
    }
}
