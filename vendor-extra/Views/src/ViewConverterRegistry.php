<?php

declare(strict_types=1);

namespace Libero\Views;

use FluentDOM\DOM\Element;

final class ViewConverterRegistry implements ViewConverter
{
    private $visitors = [];

    public function add(ViewConverterVisitor $visitor) : void
    {
        $this->visitors[] = $visitor;
    }

    public function addMany(iterable $visitors) : void
    {
        foreach ($visitors as $visitor) {
            $this->add($visitor);
        }
    }

    public function convert(Element $object, string $template, array $context = []) : View
    {
        $view = new View($template, ['attributes' => []]);

        foreach ($this->visitors as $visitor) {
            $view = $visitor->visit($object, $view, $context);
        }

        return $view;
    }
}
