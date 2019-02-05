<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use LogicException;
use function array_push;
use function is_string;

final class ViewConverterRegistry implements ViewConverter
{
    private $visitors = [];

    public function add(ViewConverterVisitor ...$visitors) : void
    {
        array_push($this->visitors, ...$visitors);
    }

    public function convert(NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : View
    {
        if (!$node instanceof Element) {
            if (is_string($template) && '@LiberoPatterns/text.html.twig' !== $template) {
                throw new LogicException(
                    "Expected the template '@LiberoPatterns/text.html.twig' for a non-element node"
                );
            }

            return new View('@LiberoPatterns/text.html.twig', ['nodes' => (string) $node]);
        }

        $view = new View($template, []);

        foreach ($this->visitors as $visitor) {
            $view = $visitor->visit($node, $view, $context);
        }

        if (!$view->getTemplate()) {
            return new View('@LiberoPatterns/text.html.twig', ['nodes' => (string) $node]);
        }

        return $view;
    }
}
