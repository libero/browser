<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Event\ChooseTemplateEvent;
use function sprintf;

trait TemplateChoosingListener
{
    use SimplifiedViewConverterListener;

    final public function onChooseTemplate(ChooseTemplateEvent $event) : void
    {
        $object = $event->getObject();

        if (!$this->canHandleElement(sprintf('{%s}%s', $object->namespaceURI, $object->localName))) {
            return;
        }

        $event->setTemplate($this->template());
    }
}
