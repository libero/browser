<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use function sprintf;

final class PubDatePartsTimeListener
{
    private const YEAR_PATH = 'jats:year[number(.)=.][1]';
    private const MONTH_PATH = 'jats:month[number(.)=.][1]';
    private const DAY_PATH = 'jats:day[number(.)=.][1]';
    private const PARTS_PATH = self::YEAR_PATH.'|'.self::MONTH_PATH.'|'.self::DAY_PATH;

    use SimplifiedViewConverterListener;

    protected function handle(Element $object, TemplateView $view) : View
    {
        $attributes = $view->getArgument('attributes') ?? [];

        if (isset($attributes['datetime'])) {
            return $view;
        }

        $xpath = $object->ownerDocument->xpath();

        $day = $xpath->firstOf(self::DAY_PATH);
        if (!$day instanceof Element) {
            return $view;
        }

        $month = $xpath->firstOf(self::MONTH_PATH);
        if (!$month instanceof Element) {
            return $view;
        }

        $year = $xpath->firstOf(self::YEAR_PATH);
        if (!$year instanceof Element) {
            return $view;
        }

        $attributes['datetime'] = sprintf("%'.04s-%'.02s-%'.02s", $year, $month, $day);

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
