<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Event\CreateViewEvent;
use function in_array;
use function is_string;
use function sprintf;

trait OptionalViewConverterListener
{
    final public function check(CreateViewEvent $event) : bool
    {
        $view = $event->getView();
        $currentTemplate = $view->getTemplate();

        if (is_string($this->expectedTemplate())) {
            if ($this->expectedTemplate() !== $currentTemplate) {
                return false;
            }
        } elseif (is_string($currentTemplate) && $this->possibleTemplate() !== $currentTemplate) {
            return false;
        }

        $object = $event->getObject();

        if (!in_array(sprintf('{%s}%s', $object->namespaceURI, $object->localName), $this->expectedElement())) {
            return false;
        }

        foreach ($this->unexpectedArguments() as $argument) {
            if ($view->hasArgument($argument)) {
                return false;
            }
        }

        if (null === $currentTemplate && is_string($this->possibleTemplate())) {
            $event->setView($view->withTemplate($this->possibleTemplate()));
        }

        return true;
    }

    protected function expectedTemplate() : ?string
    {
        return null;
    }

    protected function possibleTemplate() : ?string
    {
        return null;
    }

    /**
     * @return array<string>
     */
    abstract protected function expectedElement() : array;

    /**
     * @return array<string>
     */
    protected function unexpectedArguments() : array
    {
        return [];
    }
}
