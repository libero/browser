<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Event\CreateViewEvent;
use function in_array;
use function sprintf;

trait SimplifiedViewConverterListener
{
    final public function onCreateView(CreateViewEvent $event) : void
    {
        $view = $event->getView();

        if ($this->expectedTemplate() !== $view->getTemplate()) {
            return;
        }

        $object = $event->getObject();

        if (!in_array(sprintf('{%s}%s', $object->namespaceURI, $object->localName), $this->expectedElement())) {
            return;
        }

        foreach ($this->unexpectedArguments() as $argument) {
            if ($view->hasArgument($argument)) {
                return;
            }
        }

        $event->setView($this->handle($event));
    }

    abstract protected function handle(CreateViewEvent $event) : View;

    abstract protected function expectedTemplate() : string;

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
