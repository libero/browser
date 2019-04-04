<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use function count;
use function implode;
use function iterator_to_array;
use function Libero\LiberoPageBundle\is_valid_date;
use function Libero\ViewsBundle\array_has_key;

final class DatePartsListener
{
    private const DATE_PATTERN = '~^[0-9]{4}-[0-9]{2}-[0-9]{2}$~';

    use SimplifiedViewConverterListener;

    protected function handle(Element $object, View $view) : View
    {
        /** @var DOMNodeList<Element> $parts */
        $parts = $object('jats:year|jats:month|jats:day');

        if (3 !== count($parts)) {
            return $view;
        }

        $date = implode('-', iterator_to_array($parts));

        if (!is_valid_date($date)) {
            return $view;
        }

        return $view->withArgument('date', $date);
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
