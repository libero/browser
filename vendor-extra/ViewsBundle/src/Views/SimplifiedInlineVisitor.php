<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use function is_string;
use function sprintf;

trait SimplifiedInlineVisitor
{
    final public function visit(Element $object, View $view, array &$context = []) : View
    {
        $currentTemplate = $view->getTemplate();

        if (is_string($currentTemplate) && $this->possibleTemplate() !== $currentTemplate) {
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

        if (null === $currentTemplate) {
            $view = $view->withTemplate($this->possibleTemplate());
        }

        return $this->doVisit($object, $view, $context);
    }

    abstract protected function doVisit(Element $object, View $view, array &$context = []) : View;

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
