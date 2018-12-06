<?php

declare(strict_types=1);

namespace Libero\Views;

use FluentDOM\DOM\Node\ChildNode;

final class InlineViewConverterRegistry implements InlineViewConverter
{
    private $visitors = [];

    public function add(InlineViewConverterVisitor $visitor) : void
    {
        $this->visitors[] = $visitor;
    }

    public function addMany(iterable $visitors) : void
    {
        foreach ($visitors as $visitor) {
            $this->add($visitor);
        }
    }

    public function convert(ChildNode $object, array $context = []) : View
    {
        $view = new View('', ['attributes' => []]);

        foreach ($this->visitors as $visitor) {
            $view = $visitor->visit($object, $view, $context);
        }

        if (!$view->getTemplate()) {
            return new View('@LiberoPatterns\text.html.twig', ['nodes' => (string) $object]);
        }

        return $view;
    }
}
