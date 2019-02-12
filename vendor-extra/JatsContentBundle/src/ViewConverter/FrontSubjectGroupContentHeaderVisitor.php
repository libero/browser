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
use function implode;
use function iterator_to_array;

final class FrontSubjectGroupContentHeaderVisitor implements ViewConverterVisitor
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

        /** @var DOMNodeList|Element[] $subjects */
        $subjects = $xpath->evaluate(implode('/', [
            'jats:article-meta/jats:article-categories',
            'jats:subj-group[@subj-group-type = "heading"]/jats:subject',
        ]), $object);

        if (0 === count($subjects)) {
            return $view;
        }

        return $view->withArgument('categories', [
            'items' => array_map(
                function (string $subject) : array {
                    return ['content' => ['text' => $subject]];
                },
                iterator_to_array($subjects)
            ),
        ]);
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/content-header.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://jats.nlm.nih.gov}front';
    }

    protected function unexpectedArguments() : array
    {
        return ['categories'];
    }
}
