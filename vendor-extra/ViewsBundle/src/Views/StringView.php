<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

final class StringView implements View
{
    use ArrayAccessFromJsonSerialize;
    use HasContext;
    use IteratorFromJsonSerialize;

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

    public function jsonSerialize()
    {
        return $this->string;
    }
}
