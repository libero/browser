<?php

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class ItemParagraphVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view) : View
    {
        $title = $object->ownerDocument->xpath()
            ->firstOf(
                '/libero:item/jats:article/jats:front/jats:article-meta/jats:title-group/jats:article-title',
                $object
            );

        if (!$title instanceof Element) {
            return $view;
        }

        return $view->withArguments(
            $this->converter->convert(
                $title,
                '@LiberoPatterns/heading.html.twig',
                $view->getContext()
            )->getArguments()
        );
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/paragraph.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://libero.pub}item'];
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
