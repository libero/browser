<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use function is_string;
use function preg_match;

final class PubDateIsoDateTimeListener
{
    private const DATE_PATTERN = '~^([0-9]{4}-[0-9]{2}-[0-9]{2})(?:$|T)~';

    use SimplifiedViewConverterListener;

    protected function handle(Element $object, View $view) : View
    {
        $attributes = $view->getArgument('attributes') ?? [];

        if (isset($attributes['datetime'])) {
            return $view;
        }

        $date = $object->getAttribute('iso-8601-date');

        if (!is_string($date)) {
            return $view;
        }

        preg_match(self::DATE_PATTERN, $date, $matches);

        if (!isset($matches[1])) {
            return $view;
        }

        $attributes['datetime'] = $matches[1];

        return $view->withArguments(['attributes' => $attributes]);
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
