<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

trait SimplifiedViewConverterListener
{
    abstract protected function template() : string;

    abstract protected function canHandleElement(string $element) : bool;

    protected function canHandleArguments(array $arguments) : bool
    {
        return true;
    }
}
