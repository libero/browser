<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use function is_string;

trait HasContext
{
    private $context;

    final public function hasContext(string $key) : bool
    {
        return isset($this->context[$key]);
    }

    final public function getContext(?string $key = null)
    {
        if (is_string($key)) {
            return $this->context[$key] ?? null;
        }

        return $this->context;
    }
}
