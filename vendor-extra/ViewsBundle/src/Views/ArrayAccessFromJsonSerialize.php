<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

trait ArrayAccessFromJsonSerialize
{
    use ReadOnlyArrayAccess;

    final public function offsetExists($offset)
    {
        return isset($this->jsonSerialize()[$offset]);
    }

    final public function offsetGet($offset)
    {
        return $this->jsonSerialize()[$offset] ?? null;
    }

    abstract public function jsonSerialize();
}
