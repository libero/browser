<?php

namespace Libero\LiberoPatternsBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use function Libero\ViewsBundle\array_has_key;

final class DateLangListener
{
    use SimplifiedViewConverterListener;

    protected function handle(Element $object, View $view) : View
    {
        if (!$view->hasContext('lang')) {
            return $view;
        }

        return $view->withArgument('lang', $view->getContext('lang'));
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/date.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return true;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'lang');
    }
}
