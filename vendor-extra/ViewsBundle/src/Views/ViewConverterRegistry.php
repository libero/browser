<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node;
use LogicException;
use function array_push;
use function is_string;
use function var_dump;

final class ViewConverterRegistry implements ViewConverter
{
    private $visitors = [];

    public function add(ViewConverterVisitor ...$visitors) : void
    {
        array_push($this->visitors, ...$visitors);
    }

    public function convert(Node $node, ?string $template = null, array $context = []) : View
    {
        if (!$node instanceof Element) {
            if (is_string($template) && '@LiberoPatterns/text.html.twig' !== $template) {
                throw new LogicException(
                    "Expected the template '@LiberoPatterns/text.html.twig' for a non-element node"
                );
            }

            return new View('@LiberoPatterns/text.html.twig', ['nodes' => (string) $node], $context);
        }

        $view = new View($template, [], $context);

        foreach ($this->visitors as $visitor) {
            $view = $visitor->visit($node, $view);

            if ($view->hasContext('lazy')) {
                return $view;
            }
        }

        if (!$view->getTemplate()) {
            return new View('@LiberoPatterns/text.html.twig', ['nodes' => (string) $node], $context);
        }

        return $view;
    }
}
