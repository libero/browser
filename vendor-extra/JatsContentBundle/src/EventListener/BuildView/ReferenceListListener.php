<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function array_map;
use function count;
use function Libero\ViewsBundle\array_has_key;

final class ReferenceListListener
{
    use ConvertsLists;
    use ViewBuildingListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        /** @var DOMNodeList<Element> $references */
        $references = $object('jats:ref/jats:element-citation[jats:article-title]');

        if (0 === count($references)) {
            return $view;
        }

        return $view->withArgument(
            'items',
            array_map(
                static function (View $view) : array {
                    return ['content' => $view instanceof TemplateView ? $view->getArguments() : $view];
                },
                $this->convertList($references, '@LiberoPatterns/reference.html.twig', $view->getContext())
            )
        );
    }

    protected function template() : string
    {
        return '@LiberoPatterns/reference-list.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}ref-list' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'items');
    }
}
