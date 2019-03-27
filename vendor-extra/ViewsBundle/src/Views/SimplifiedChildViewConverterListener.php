<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Event\CreateViewEvent;
use function is_string;
use function sprintf;

trait SimplifiedChildViewConverterListener
{
    final public function onCreateView(CreateViewEvent $event) : void
    {
        $view = $event->getView();

        $currentTemplate = $view->getTemplate();

        if (is_string($currentTemplate) && $this->possibleTemplate() !== $currentTemplate) {
            return;
        }

        $object = $event->getObject();

        if ($this->expectedElement() !== sprintf('{%s}%s', $object->namespaceURI, $object->localName)) {
            return;
        }

        foreach ($this->unexpectedArguments() as $argument) {
            if ($view->hasArgument($argument)) {
                return;
            }
        }

        if (null === $currentTemplate) {
            $event->setView($view->withTemplate($this->possibleTemplate()));
        }

        $event->setView($this->handle($event));
    }

    abstract protected function handle(CreateViewEvent $event) : View;

    abstract protected function possibleTemplate() : string;

    abstract protected function expectedElement() : string;

    /**
     * @return array<string>
     */
    protected function unexpectedArguments() : array
    {
        return [];
    }
}
