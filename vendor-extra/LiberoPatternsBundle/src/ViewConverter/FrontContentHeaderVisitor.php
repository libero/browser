<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\ViewConverter;

use DOMNodeList;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class FrontContentHeaderVisitor implements ViewConverterVisitor
{
    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function visit(Element $object, View $view, array &$context = []) : View
    {
        if ('@LiberoPatterns/content-header.html.twig' !== $view->getTemplate()) {
            return $view;
        }

        if ('front' !== $object->nodeName || 'http://libero.pub' !== $object->namespaceURI) {
            return $view;
        }

        /** @var Document $document */
        $document = $object->ownerDocument;
        $document->xpath()->registerNamespace('libero', 'http://libero.pub');

        /** @var DOMNodeList|Element[] $titleList */
        $titleList = $object('libero:title[1]');
        $title = $titleList->item(0);

        if (!$title instanceof Element) {
            return $view;
        }

        if ($view->hasArgument('contentTitle')) {
            return $view;
        }

        return $view->withArgument(
            'contentTitle',
            $this->converter->convert($title, '@LiberoPatterns/heading.html.twig', $context)->getArguments()
        );
    }
}
