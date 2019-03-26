<?php

namespace Libero\LiberoPageBundle\ViewConverter;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class ItemListVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view) : View
    {
        return $view->withArgument(
            'nodes',
            array_map(
                function (Node $child) use ($view) : View {
                    return $this->converter->convert(
                        $child,
                        '@LiberoPatterns/paragraph.html.twig',
                        $view->getContext()
                    );
                },
                iterator_to_array($object->getElementsByTagNameNS('http://libero.pub', 'item-ref'))
            )
        );
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/text.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://libero.pub}item-list'];
    }

    protected function unexpectedArguments() : array
    {
        return ['nodes'];
    }
}
