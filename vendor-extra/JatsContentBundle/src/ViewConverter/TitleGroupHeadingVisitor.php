<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class TitleGroupHeadingVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        /** @var Document $document */
        $document = $object->ownerDocument;
        $xpath = $document->xpath();
        $xpath->registerNamespace('jats', 'http://jats.nlm.nih.gov');

        $title = $xpath->firstOf('jats:article-title', $object);

        if (!$title instanceof Element) {
            return $view;
        }

        return $view->withArgument('text', (string) $title);
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/heading.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://jats.nlm.nih.gov}title-group';
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
