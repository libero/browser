<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\ViewConverter\Inline;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsInlineNodes;
use Libero\ViewsBundle\Views\InlineViewConverter;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class ItalicVisitor implements ViewConverterVisitor
{
    use ConvertsInlineNodes;
    use SimplifiedVisitor;

    private $inlineConverter;

    public function __construct(InlineViewConverter $inlineConverter)
    {
        $this->inlineConverter = $inlineConverter;
    }

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        return $view->withArgument('text', $this->convertInlineNodes($object, $context));
    }

    protected function possibleTemplate() : ?string
    {
        return '@LiberoPatterns/italic.html.twig';
    }

    protected function expectedElement() : ?string
    {
        return '{http://libero.pub}i';
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
