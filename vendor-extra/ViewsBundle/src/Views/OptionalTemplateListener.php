<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use function is_string;

trait OptionalTemplateListener
{
    use SimplifiedViewConverterListener;

    abstract protected function template() : string;

    final protected function canHandleTemplate(?string $template) : bool
    {
        return !is_string($template) || $this->template() === $template;
    }

    final protected function beforeHandle(View $view) : View
    {
        return $view->withTemplate($this->template());
    }
}
