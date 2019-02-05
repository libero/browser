<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use DOMNodeList;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use function array_map;
use function count;
use function iterator_to_array;

final class FrontItemTagsVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        /** @var Document $document */
        $document = $object->ownerDocument;
        $xpath = $document->xpath();
        $xpath->registerNamespace('jats', 'http://jats.nlm.nih.gov');

        /** @var DOMNodeList|Element[] $keywordGroups */
        $keywordGroups = $xpath->evaluate('jats:article-meta/jats:kwd-group', $object);

        if (0 === count($keywordGroups)) {
            return $view;
        }

        return $view->withArgument(
            'groups',
            array_map(
                function (Element $keywordGroup) use ($context) {
                    return $this->converter
                        ->convert($keywordGroup, '@LiberoPatterns/tag-list.html.twig', $context)
                        ->getArguments();
                },
                iterator_to_array($keywordGroups)
            )
        );
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/item-tags.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://jats.nlm.nih.gov}front';
    }

    protected function unexpectedArguments() : array
    {
        return ['groups'];
    }
}
