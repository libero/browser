<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use ArrayAccess;
use IteratorAggregate;
use function call_user_func;

final class LazyView implements ArrayAccess, IteratorAggregate, View
{
    use IteratorArrayAccess;
    use HasContext;

    private $callback;
    private $view;

    public function __construct(callable $callback, array $context = [])
    {
        $this->callback = $callback;
        $this->context = $context;
    }

    public function getIterator()
    {
        return $this->resolve();
    }

    private function resolve() : TemplateView
    {
        if (isset($this->callback)) {
            $this->view = call_user_func($this->callback);
            $this->callback = null;
        }

        return $this->view;
    }
}
