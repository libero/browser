<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class ParagraphVisitor implements ViewConverterVisitor
{
    use ConvertsChildren;
    use SimplifiedVisitor;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, View $view) : View
    {
        return $view->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function possibleTemplate() : string
    {
        return '@LiberoPatterns/paragraph.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://jats.nlm.nih.gov}p'];
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
