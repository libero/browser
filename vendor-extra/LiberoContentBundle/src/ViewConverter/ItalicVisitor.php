<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedChildVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class ItalicVisitor implements ViewConverterVisitor
{
    use ConvertsChildren;
    use SimplifiedChildVisitor;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view) : View
    {
        return $view->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function possibleTemplate() : string
    {
        return '@LiberoPatterns/italic.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://libero.pub}italic';
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
