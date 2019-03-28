<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use function sprintf;

trait SimplifiedVisitor
{
    final public function visit(Element $object, View $view) : View
    {
        if (!$this->canHandleTemplate($view->getTemplate())) {
            return $view;
        }

        if (!$this->canHandleElement(sprintf('{%s}%s', $object->namespaceURI, $object->localName))) {
            return $view;
        }

        if (!$this->canHandleArguments($view->getArguments())) {
            return $view;
        }

        $view = $this->beforeHandle($view);

        return $this->handle($object, $view);
    }

    abstract protected function handle(Element $object, View $view) : View;

    abstract protected function canHandleTemplate(?string $template) : bool;

    abstract protected function canHandleElement(string $element) : bool;

    protected function canHandleArguments(array $arguments) : bool
    {
        return true;
    }

    protected function beforeHandle(View $view) : View
    {
        return $view;
    }
}
