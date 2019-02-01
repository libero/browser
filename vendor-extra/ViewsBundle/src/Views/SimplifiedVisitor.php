<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use function is_string;
use function sprintf;

trait SimplifiedVisitor
{
    final public function visit(Element $object, View $view, array &$context = []) : View
    {
        if ($this->expectedTemplate() !== $view->getTemplate()) {
            return $view;
        }

        $expectedElement = $this->expectedElement();
        $actualElement = sprintf('{%s}%s', $object->namespaceURI, $object->localName);

        if (is_string($expectedElement) && $expectedElement !== $actualElement) {
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

    protected function expectedElement() : ?string
    {
        return null;
    }

    /**
     * @return array<string>
     */
    protected function unexpectedArguments() : array
    {
        return [];
    }
}
