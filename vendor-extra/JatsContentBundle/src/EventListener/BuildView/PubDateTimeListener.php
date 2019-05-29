<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use IntlDateFormatter;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use function Libero\ViewsBundle\array_has_key;
use function strtotime;

final class PubDateTimeListener
{
    use ViewBuildingListener;

    protected function handle(Element $object, TemplateView $view) : View
    {
        if (!isset($view->getArgument('attributes')['datetime']) || !$view->hasContext('lang')) {
            return $view;
        }

        $formatter = IntlDateFormatter::create(
            $view->getContext('lang'),
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE
        );

        $text = $formatter->format(strtotime($view->getArgument('attributes')['datetime']));

        if (!$text) {
            return $view;
        }

        return $view->withArgument('text', $text);
    }

    protected function template() : string
    {
        return '@LiberoPatterns/time.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}pub-date' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'text');
    }
}
