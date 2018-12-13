<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use function array_push;

final class InlineViewConverterRegistry implements InlineViewConverter
{
    private $visitors = [];

    public function add(InlineViewConverterVisitor ...$visitors) : void
    {
        array_push($this->visitors, ...$visitors);
    }

    public function convert(NonDocumentTypeChildNode $object, array $context = []) : View
    {
        $view = new View('', ['attributes' => []]);

        foreach ($this->visitors as $visitor) {
            $view = $visitor->visit($object, $view, $context);
        }

        if (!$view->getTemplate()) {
            return new View('@LiberoPatterns/text.html.twig', ['nodes' => (string) $object]);
        }

        return $view;
    }
}
