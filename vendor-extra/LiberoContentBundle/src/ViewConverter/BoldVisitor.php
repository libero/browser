<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\OptionalTemplateVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use function Libero\ViewsBundle\array_has_key;

final class BoldVisitor implements ViewConverterVisitor
{
    use ConvertsChildren;
    use OptionalTemplateVisitor;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, View $view) : View
    {
        return $view->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function template() : string
    {
        return '@LiberoPatterns/bold.html.twig';
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://libero.pub}bold' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'text');
    }
}
