<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use function count;
use function iterator_to_array;
use function sprintf;
use function usort;

final class PubDatePartsTimeListener
{
    private const YEAR_PATH = 'jats:year[number(.)=.][1]';
    private const MONTH_PATH = 'jats:month[number(.)=.][1]';
    private const DAY_PATH = 'jats:day[number(.)=.][1]';
    private const PARTS_PATH = self::YEAR_PATH.'|'.self::MONTH_PATH.'|'.self::DAY_PATH;

    use SimplifiedViewConverterListener;

    protected function handle(Element $object, View $view) : View
    {
        $attributes = $view->getArgument('attributes') ?? [];

        if (isset($attributes['datetime'])) {
            return $view;
        }

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

        $attributes['datetime'] = sprintf("%'.04s-%'.02s-%'.02s", ...$parts);

        return $view->withArgument('attributes', $attributes);
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/time.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://jats.nlm.nih.gov}pub-date' === $element;
    }
}
