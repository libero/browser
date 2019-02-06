<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use function implode;

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

        // @todo - add support for other ways of expressing a subject group in JATS.
        $groups = $xpath->evaluate(implode('/', [
            'jats:article-meta/jats:article-categories',
            'jats:subj-group[@subj-group-type = "heading"]/jats:subject',
        ]), $object);

        if ($groups instanceof DOMNodeList && $groups->count() === 0) {
            return $view;
        }

        $items = [];
        foreach ($groups as $group) {
            $items[] = [
                'content' => [
                    // @todo - needs to support inline HTML.
                    'text' => (string) $group,
                ],
            ];
        }

        return $view->withArgument('categories', [
            'items' => $items,
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
