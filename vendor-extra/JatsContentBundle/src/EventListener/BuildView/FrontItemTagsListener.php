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
use function array_filter;
use function array_map;
use function array_values;
use function count;
use function Libero\ViewsBundle\array_has_key;

final class FrontItemTagsListener
{
    use ConvertsLists;
    use ViewBuildingListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        /** @var DOMNodeList<Element> $keywordGroups */
        $keywordGroups = $object->ownerDocument->xpath()
            ->evaluate('jats:article-meta/jats:kwd-group', $object);

        if (0 === count($keywordGroups)) {
            return $view;
        }

        $groups = array_values(
            array_filter(
                array_map(
                    static function (View $tagList) : array {
                        return $tagList instanceof TemplateView ? $tagList->getArguments() : [];
                    },
                    $this->convertList($keywordGroups, '@LiberoPatterns/tag-list.html.twig', $view->getContext())
                )
            )
        );

        if (0 === count($groups)) {
            return $view;
        }

        return $view->withArgument('groups', $groups);
    }

    protected function template() : string
    {
        return '@LiberoPatterns/item-tags.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}front' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'groups');
    }
}
