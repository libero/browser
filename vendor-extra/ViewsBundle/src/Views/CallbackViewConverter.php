<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use function call_user_func;

final class CallbackViewConverter implements ViewConverter
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function convert(Element $object, ?string $template, array $context = []) : View
    {
        return call_user_func($this->callback, $object, $template, $context);
    }
}
