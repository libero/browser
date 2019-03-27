<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use LogicException;
use function in_array;
use function is_string;
use function sprintf;

trait SimplifiedVisitor
{
    final public function visit(Element $object, View $view) : View
    {
        if (is_string($this->expectedTemplate()) && $this->expectedTemplate() !== $view->getTemplate()) {
            return $view;
        }

        if (is_string($view->getTemplate()) && $this->possibleTemplate() !== $view->getTemplate()) {
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

        if (null === $view->getTemplate()) {
            $view = $view->withTemplate($this->possibleTemplate());
        }

        return $this->doVisit($object, $view);
    }

    abstract protected function doVisit(Element $object, View $view) : View;

    protected function expectedTemplate() : ?string
    {
        return null;
    }

    protected function possibleTemplate() : string
    {
        if (!is_string($this->expectedTemplate())) {
            throw new LogicException('Visitor must override possibleTemplate() if it does not expectedTemplate()');
        }

        return $this->expectedTemplate();
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
