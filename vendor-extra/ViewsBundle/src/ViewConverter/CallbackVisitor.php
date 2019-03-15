<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use function call_user_func_array;

final class CallbackVisitor implements ViewConverterVisitor
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function visit(Element $object, View $view) : View
    {
        return call_user_func_array($this->callback, [$object, $view]);
    }
}
