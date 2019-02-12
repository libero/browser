<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use DOMNodeList;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use function array_map;
use function count;

final class FrontItemTagsVisitor implements ViewConverterVisitor
{
    use ConvertsLists;
    use SimplifiedVisitor;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        /** @var Document $document */
        $document = $object->ownerDocument;

        /** @var DOMNodeList|Element[] $keywordGroups */
        $keywordGroups = $document->xpath()->evaluate('jats:article-meta/jats:kwd-group[@kwd-group-type]', $object);

        if (0 === count($keywordGroups)) {
            return $view;
        }

        return $view->withArgument(
            'groups',
            array_map(
                function (View $tagList) : array {
                    return $tagList->getArguments();
                },
                $this->convertList($keywordGroups, '@LiberoPatterns/tag-list.html.twig', $context)
            )
        );
    }

    protected function expectedTemplate() : string
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
