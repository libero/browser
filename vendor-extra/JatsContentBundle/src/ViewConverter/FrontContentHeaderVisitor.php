<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class FrontContentHeaderVisitor implements ViewConverterVisitor
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

        $title = $xpath->firstOf('jats:article-meta/jats:title-group/jats:article-title', $object);

        if (!$title instanceof Element) {
            return $view;
        }

        $view = $view->withArgument(
            'contentTitle',
            $this->converter->convert($title, '@LiberoPatterns/heading.html.twig', $context)->getArguments()
        );

        $categories = [];
        foreach ($xpath->evaluate('jats:article-meta/jats:article-categories/jats:subj-group[@subj-group-type = "heading"]/jats:subject', $object) as $category) {
            $categories['items'][] = ['content' => ['text' => $category]];
        }

        if (!empty($categories)) {
            $view = $view->withArgument(
                'categories',
                $categories
            );
        }

        return $view;
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
        return ['contentTitle'];
    }
}
