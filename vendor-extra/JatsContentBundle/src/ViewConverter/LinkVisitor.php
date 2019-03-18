<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class LinkVisitor implements ViewConverterVisitor
{
    use ConvertsChildren;
    use SimplifiedVisitor;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view) : View
    {
        return $view->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/link.html.twig';
    }

    protected function expectedElement() : array
    {
        return [
            '{http://jats.nlm.nih.gov}kwd',
            '{http://jats.nlm.nih.gov}subject',
        ];
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
