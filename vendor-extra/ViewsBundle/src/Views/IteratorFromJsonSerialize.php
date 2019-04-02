<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use ArrayIterator;

trait IteratorFromJsonSerialize
{
    public function getIterator()
    {
        return new ArrayIterator((array) $this->jsonSerialize());
    }

    abstract public function jsonSerialize();
}
