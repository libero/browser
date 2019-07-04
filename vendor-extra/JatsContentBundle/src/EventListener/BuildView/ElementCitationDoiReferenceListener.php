<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use function Libero\ViewsBundle\array_has_key;

final class ElementCitationDoiReferenceListener
{
    use ViewBuildingListener;

    protected function handle(Element $object, TemplateView $view) : View
    {
        $doi = $object->ownerDocument->xpath()
            ->firstOf('jats:pub-id[@pub-id-type="doi"]', $object);

        if (!$doi instanceof Element) {
            return $view;
        }

        return $view->withArgument('doi', (string) $doi);
    }

    protected function template() : string
    {
        return '@LiberoPatterns/reference.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}element-citation' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'doi');
    }
}
