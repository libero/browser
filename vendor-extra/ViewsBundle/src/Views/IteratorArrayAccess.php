<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use function iterator_to_array;

trait IteratorArrayAccess
{
    use ReadOnlyArrayAccess;

    final public function offsetExists($offset)
    {
        return isset(iterator_to_array($this)[$offset]);
    }

    final public function offsetGet($offset)
    {
        return iterator_to_array($this)[$offset] ?? null;
    }
}
