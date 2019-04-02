<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

final class EmptyView implements View
{
    use ArrayAccessFromJsonSerialize;
    use HasContext;
    use IteratorFromJsonSerialize;

    public function __construct(array $context = [])
    {
        $this->context = $context;
    }

    public function __toString() : string
    {
        return '';
    }

    public function jsonSerialize()
    {
        return null;
    }
}
