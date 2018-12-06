<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\ViewConverter;

use DOMNodeList;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\ChildNode;
use Libero\Views\InlineViewConverter;
use Libero\Views\LangAttributes;
use Libero\Views\View;
use Libero\Views\ViewConverterVisitor;
use function Functional\map;

final class FrontContentHeaderVisitor implements ViewConverterVisitor
{
    use LangAttributes;

    private $inlineConverter;

    public function __construct(InlineViewConverter $inlineConverter)
    {
        $this->inlineConverter = $inlineConverter;
    }

    public function visit(Element $object, View $view, array &$context = []) : View
    {
        if ('@LiberoPatterns/content-header.html.twig' !== $view->getTemplate()) {
            return $view;
        }

        if ('front' !== $object->nodeName || 'http://libero.pub' !== $object->namespaceURI) {
            return $view;
        }

        /** @var DOMNodeList|Element[] $titleList */
        $titleList = $object('libero:title[1]');
        $title = $titleList->item(0);

        if (!$title instanceof Element) {
            return $view;
        }

        $titleContext = $context;

        $arguments = [
            'contentTitle' => [
                'attributes' => $this->addLangAttribute($title, $titleContext),
                'text' => map(
                    $title,
                    function (ChildNode $node) use ($titleContext) : ?View {
                        return $this->inlineConverter->convert($node, $titleContext);
                    }
                ),
            ],
        ];

        return $view->withArguments($arguments);
    }
}
