<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\ViewConverter\Inline;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\ChildNode;
use Libero\Views\InlineViewConverter;
use Libero\Views\InlineViewConverterVisitor;
use Libero\Views\View;
use function Functional\map;

final class ItalicVisitor implements InlineViewConverterVisitor
{
    private $inlineConverter;

    public function __construct(InlineViewConverter $inlineConverter)
    {
        $this->inlineConverter = $inlineConverter;
    }

    public function visit(ChildNode $object, View $view, array &$context = []) : View
    {
        if (!$object instanceof Element || 'i' !== $object->nodeName || 'http://libero.pub' !== $object->namespaceURI) {
            return $view;
        }

        if ($view->getTemplate() && '@LiberoPatterns/italic.html.twig' !== !$view->getTemplate()) {
            return $view;
        }

        if (!$view->getTemplate()) {
            $view = $view->withTemplate('@LiberoPatterns/italic.html.twig');
        }

        $arguments = [];

        if (!$view->hasArgument('text')) {
            $arguments['text'] = map(
                $object,
                function (ChildNode $node) use ($context) : ?View {
                    return $this->inlineConverter->convert($node, $context);
                }
            );
        }

        return $view->withArguments($arguments);
    }
}
