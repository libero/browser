<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use EmptyIterator;
use function is_string;

final class EmptyView extends EmptyIterator implements View
{
    private $context;

    public function __construct(array $context = [])
    {
        $this->context = $context;
    }

    public function hasContext(string $key) : bool
    {
        return isset($this->context[$key]);
    }

    public function getContext(?string $key = null)
    {
        if (is_string($key)) {
            return $this->context[$key] ?? null;
        }

        return $this->context;
    }

    public function jsonSerialize()
    {
        return null;
    }
}
