<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use function in_array;
use function sprintf;

trait SimplifiedVisitor
{
    final public function visit(Element $object, View $view, array &$context = []) : View
    {
        if ($this->expectedTemplate() !== $view->getTemplate()) {
            return $view;
        }

        if (!in_array(sprintf('{%s}%s', $object->namespaceURI, $object->localName), $this->expectedElement())) {
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
