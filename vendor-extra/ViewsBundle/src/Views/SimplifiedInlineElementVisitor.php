<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use function sprintf;

trait SimplifiedInlineElementVisitor
{
    final public function visit(NonDocumentTypeChildNode $object, View $view, array &$context = []) : View
    {
        if (!$object instanceof Element) {
            return $view;
        }

        if ($view->getTemplate() && $this->expectedTemplate() !== !$view->getTemplate()) {
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

        if (!$view->getTemplate()) {
            $view = $view->withTemplate($this->expectedTemplate());
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
