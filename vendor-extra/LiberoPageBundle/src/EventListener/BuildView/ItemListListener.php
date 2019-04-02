<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function array_map;
use function iterator_to_array;
use function Libero\ViewsBundle\array_has_key;

final class ItemListListener
{
    use SimplifiedViewConverterListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, View $view) : View
    {
        return $view->withArgument(
            'nodes',
            array_map(
                function (NonDocumentTypeChildNode $child) use ($view) : View {
                    return $this->converter->convert(
                        $child,
                        '@LiberoPatterns/teaser.html.twig',
                        $view->getContext()
                    );
                },
                iterator_to_array($object->getElementsByTagNameNS('http://libero.pub', 'item-ref'))
            )
        );
    }

    protected function canHandleTemplate(string $template) : bool
    {
        return '@LiberoPatterns/text.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://libero.pub}item-list' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'nodes');
    }
}
