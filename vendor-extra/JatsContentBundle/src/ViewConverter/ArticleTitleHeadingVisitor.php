<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class ArticleTitleHeadingVisitor implements ViewConverterVisitor
{
    use ConvertsChildren;
    use SimplifiedVisitor;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        return $view->withArgument('text', $this->convertChildren($object, $context));
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/heading.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://jats.nlm.nih.gov}article-title';
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
