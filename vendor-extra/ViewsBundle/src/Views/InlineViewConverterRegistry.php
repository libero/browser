<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use function array_map;
use function array_push;
use function iterator_to_array;

final class InlineViewConverterRegistry implements InlineViewConverter
{
    private $visitors = [];

    public function add(InlineViewConverterVisitor ...$visitors) : void
    {
        array_push($this->visitors, ...$visitors);
    }

    public function convert(NonDocumentTypeChildNode $object, array $context = []) : View
    {
        $view = new View(null, []);

        foreach ($this->visitors as $visitor) {
            $view = $visitor->visit($object, $view, $context);
        }

        if (!$view->getTemplate()) {
            return new View('@LiberoPatterns/text.html.twig', ['nodes' => (string) $object]);
        }

        return $view;
    }

    public function convertChildren(Element $object, array $context = []) : array
    {
        return array_map(
            function (NonDocumentTypeChildNode $child) use ($context) : View {
                return $this->convert($child, $context);
            },
            iterator_to_array($object)
        );
    }
}
