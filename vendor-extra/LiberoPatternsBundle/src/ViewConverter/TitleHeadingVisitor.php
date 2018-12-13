<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\ViewConverter;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\ChildNode;
use Libero\ViewsBundle\Views\InlineViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use function Functional\map;

final class TitleHeadingVisitor implements ViewConverterVisitor
{
    private $inlineConverter;

    public function __construct(InlineViewConverter $inlineConverter)
    {
        $this->inlineConverter = $inlineConverter;
    }

    public function visit(Element $object, View $view, array &$context = []) : View
    {
        if ('@LiberoPatterns/heading.html.twig' !== $view->getTemplate()) {
            return $view;
        }

        if ('title' !== $object->nodeName || 'http://libero.pub' !== $object->namespaceURI) {
            return $view;
        }

        return $view->withArgument(
            'text',
            map(
                $object,
                function (ChildNode $node) use ($context) : ?View {
                    return $this->inlineConverter->convert($node, $context);
                }
            )
        );
    }
}
