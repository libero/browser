<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use UnexpectedValueException;
use function is_string;
use function sprintf;

trait SimplifiedVisitor
{
    final public function visit(Element $object, View $view, array &$context = []) : View
    {
        $currentTemplate = $view->getTemplate();

        if (is_string($currentTemplate) && $this->expectedTemplate() !== $currentTemplate) {
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

        if (null === $currentTemplate) {
            if (null === $this->possibleTemplate()) {
                throw new UnexpectedValueException();
            }

            $view = $view->withTemplate($this->possibleTemplate());
        }

        return $this->doVisit($object, $view, $context);
    }

    abstract protected function doVisit(Element $object, View $view, array &$context = []) : View;

    protected function expectedTemplate() : ?string
    {
        return null;
    }

    protected function possibleTemplate() : ?string
    {
        return null;
    }

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
