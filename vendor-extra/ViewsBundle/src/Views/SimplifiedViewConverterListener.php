<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;

trait SimplifiedViewConverterListener
{
    abstract protected function template() : string;

    abstract protected function canHandleElement(Element $element) : bool;

    protected function canHandleArguments(array $arguments) : bool
    {
        return true;
    }
}
