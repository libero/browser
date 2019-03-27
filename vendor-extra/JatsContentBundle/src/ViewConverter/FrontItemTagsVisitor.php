<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use function array_filter;
use function array_map;
use function array_values;
use function count;

final class FrontItemTagsVisitor implements ViewConverterVisitor
{
    use ConvertsLists;
    use SimplifiedVisitor;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, View $view) : View
    {
        /** @var DOMNodeList|Element[] $keywordGroups */
        $keywordGroups = $object->ownerDocument->xpath()
            ->evaluate('jats:article-meta/jats:kwd-group', $object);

        if (0 === count($keywordGroups)) {
            return $view;
        }

        $groups = array_values(
            array_filter(
                array_map(
                    function (View $tagList) : array {
                        return $tagList->getArguments();
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

    protected function expectedTemplate() : ?string
    {
        return '@LiberoPatterns/item-tags.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://jats.nlm.nih.gov}front'];
    }

    protected function unexpectedArguments() : array
    {
        return ['groups'];
    }
}
