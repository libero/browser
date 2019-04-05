<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use function count;
use function iterator_to_array;
use function Libero\ViewsBundle\array_has_key;
use function sprintf;
use function usort;

final class DatePartsListener
{
    private const YEAR_PATH = 'jats:year[number(.)=.][1]';
    private const MONTH_PATH = 'jats:month[number(.)=.][1]';
    private const DAY_PATH = 'jats:day[number(.)=.][1]';
    private const PARTS_PATH = self::YEAR_PATH.'|'.self::MONTH_PATH.'|'.self::DAY_PATH;

    use SimplifiedViewConverterListener;

    protected function handle(Element $object, View $view) : View
    {
        /** @var DOMNodeList<Element> $parts */
        $parts = $object(self::PARTS_PATH);

        if (3 !== count($parts)) {
            return $view;
        }

        $parts = iterator_to_array($parts);
        usort(
            $parts,
            function (Element $a, Element $b) : int {
                if ('year' === $a->localName) {
                    return -1;
                }
                if ('year' === $b->localName) {
                    return 1;
                }
                if ('month' === $a->localName) {
                    return -1;
                }
                if ('month' === $b->localName) {
                    return 1;
                }

                return 0;
            }
        );

        return $view->withArgument('date', sprintf("%'.04s-%'.02s-%'.02s", ...$parts));
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
