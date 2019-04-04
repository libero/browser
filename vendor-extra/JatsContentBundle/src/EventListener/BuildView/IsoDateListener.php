<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use function is_string;
use function Libero\LiberoPageBundle\is_valid_date;
use function Libero\ViewsBundle\array_has_key;
use function preg_match;

final class IsoDateListener
{
    private const DATE_PATTERN = '~^([0-9]{4}-[0-9]{2}-[0-9]{2})(?:$|T)~';

    use SimplifiedViewConverterListener;

    protected function handle(Element $object, View $view) : View
    {
        $date = $object->getAttribute('iso-8601-date');

        if (!is_string($date)) {
            return $view;
        }

        preg_match(self::DATE_PATTERN, $date, $matches);

        if (!isset($matches[1]) || !is_valid_date($matches[1])) {
            return $view;
        }

        return $view->withArgument('date', $matches[1]);
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/date.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://jats.nlm.nih.gov}pub-date' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'date');
    }
}
