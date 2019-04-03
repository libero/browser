<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

trait SimplifiedArrayAccess
{
    use ReadOnlyArrayAccess;

    final public function offsetExists($offset)
    {
        return isset($this->asArray()[$offset]);
    }

    final public function offsetGet($offset)
    {
        return $this->asArray()[$offset] ?? null;
    }

    abstract protected function asArray() : array;
}
