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

final class FrontSubjectGroupContentHeaderVisitor implements ViewConverterVisitor
{
    use ConvertsLists;
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

        /** @var DOMNodeList|Element[] $subjects */
        $subjects = $document->xpath()->evaluate(
            'jats:article-meta/jats:article-categories/jats:subj-group[@subj-group-type="heading"]/jats:subject',
            $object
        );

        if (0 === count($subjects)) {
            return $view;
        }

        return $view->withArgument(
            'categories',
            [
                'items' => array_map(
                    function (View $link) : array {
                        return ['content' => $link->getArguments()];
                    },
                    $this->convertList($subjects, '@LiberoPatterns/link.html.twig', $context)
                ),
            ]
        );
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/content-header.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://jats.nlm.nih.gov}front'];
    }

    protected function unexpectedArguments() : array
    {
        return ['categories'];
    }
}
