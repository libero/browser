<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use function array_push;

final class ViewConverterRegistry implements ViewConverter
{
    private $visitors = [];

    public function add(ViewConverterVisitor ...$visitors) : void
    {
        array_push($this->visitors, ...$visitors);
    }

    public function convert(Element $object, ?string $template, array $context = []) : View
    {
        $view = new View($template, ['attributes' => []]);

        foreach ($this->visitors as $visitor) {
            $view = $visitor->visit($object, $view, $context);
        }

        return $view;
    }
}
