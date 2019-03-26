<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Node;
use function call_user_func;

final class CallbackViewConverter implements ViewConverter
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function convert(Node $node, ?string $template = null, array $context = []) : View
    {
        return call_user_func($this->callback, $node, $template, $context);
    }
}
