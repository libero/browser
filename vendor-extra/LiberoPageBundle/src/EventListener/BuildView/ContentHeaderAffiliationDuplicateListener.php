<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use function array_unique;
use function array_values;
use function Libero\ViewsBundle\array_has_key;
use const SORT_REGULAR;

final class ContentHeaderAffiliationDuplicateListener
{
    use ViewBuildingListener;

    protected function handle(Element $object, TemplateView $view) : View
    {
        $affiliations = $view->getArgument('affiliations');

        $affiliations['items'] = array_values(
            array_unique(
                $view->getArgument('affiliations')['items'] ?? [],
                SORT_REGULAR
            )
        );

        return $view->withArgument('affiliations', $affiliations);
    }

    protected function template() : string
    {
        return '@LiberoPatterns/content-header.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return true;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return array_has_key($arguments, 'affiliations');
    }
}
