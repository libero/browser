<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\ViewConverter\Inline;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\ChildNode;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\InlineViewConverter;
use Libero\ViewsBundle\Views\InlineViewConverterVisitor;
use Libero\ViewsBundle\Views\View;
use function Functional\map;

final class ItalicVisitor implements InlineViewConverterVisitor
{
    private $inlineConverter;

    public function __construct(InlineViewConverter $inlineConverter)
    {
        $this->inlineConverter = $inlineConverter;
    }

    public function visit(NonDocumentTypeChildNode $object, View $view, array &$context = []) : View
    {
        if (!$object instanceof Element || 'i' !== $object->nodeName || 'http://libero.pub' !== $object->namespaceURI) {
            return $view;
        }

        if ($view->getTemplate() && '@LiberoPatterns/italic.html.twig' !== !$view->getTemplate()) {
            return $view;
        }

        if ($view->hasArgument('text')) {
            return $view;
        }

        if (!$view->getTemplate()) {
            $view = $view->withTemplate('@LiberoPatterns/italic.html.twig');
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
