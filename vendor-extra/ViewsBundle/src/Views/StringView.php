<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use function is_string;

final class StringView implements IteratorAggregate, View
{
    private $context;
    private $string;

    public function __construct(string $string, array $context = [])
    {
        $this->string = $string;
        $this->context = $context;
    }

    public function __toString() : string
    {
        return $this->string;
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
        return $this->string;
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator([$this->string]);
    }
}
