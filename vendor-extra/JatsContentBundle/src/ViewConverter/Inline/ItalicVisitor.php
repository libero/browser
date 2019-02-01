<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter\Inline;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\InlineViewConverter;
use Libero\ViewsBundle\Views\InlineViewConverterVisitor;
use Libero\ViewsBundle\Views\SimplifiedInlineElementVisitor;
use Libero\ViewsBundle\Views\View;

final class ItalicVisitor implements InlineViewConverterVisitor
{
    use SimplifiedInlineElementVisitor;

    private $inlineConverter;

    public function __construct(InlineViewConverter $inlineConverter)
    {
        $this->inlineConverter = $inlineConverter;
    }

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        return $view->withArgument('text', $this->inlineConverter->convertChildren($object, $context));
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/italic.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://jats.nlm.nih.gov}italic';
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
