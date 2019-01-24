<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use function sprintf;

trait SimplifiedVisitor
{
    final public function visit(Element $object, View $view, array &$context = []) : View
    {
        if ($this->expectedTemplate() !== $view->getTemplate()) {
            return $view;
        }

        if ($this->expectedElement() !== sprintf('{%s}%s', $object->namespaceURI, $object->localName)) {
            return $view;
        }

        foreach ($this->unexpectedArguments() as $argument) {
            if ($view->hasArgument($argument)) {
                return $view;
            }
        }

        return $this->doVisit($object, $view, $context);
    }

    abstract protected function doVisit(Element $object, View $view, array &$context = []) : View;

    abstract protected function expectedTemplate() : string;

    abstract protected function expectedElement() : string;

    /**
     * @return array<string>
     */
    protected function unexpectedArguments() : array
    {
        return [];
    }
}
