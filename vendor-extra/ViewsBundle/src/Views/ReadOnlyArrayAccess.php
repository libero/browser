<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use BadMethodCallException;

trait ReadOnlyArrayAccess
{
    final public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException(__CLASS__.' is immutable');
    }

    final public function offsetUnset($offset)
    {
        throw new BadMethodCallException(__CLASS__.' is immutable');
    }
}
