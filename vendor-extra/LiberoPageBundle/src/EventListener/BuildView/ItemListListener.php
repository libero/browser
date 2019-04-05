<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\LazyView;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\TemplateView;
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

    protected function handle(Element $object, TemplateView $view) : View
    {
        $items = array_map(
            function (NonDocumentTypeChildNode $child) use ($view) : View {
                return $this->converter->convert(
                        $child,
                        '@LiberoPatterns/teaser.html.twig',
                        $view->getContext()
                    );
            },
            iterator_to_array($object->getElementsByTagNameNS('http://libero.pub', 'item-ref'))
        );

        return new LazyView(function () use ($view, $items) {
            return $view->withArgument(
                'list',
                [
                    'items' => array_map(
                        function (View $view) {
                            return ['content' => $view['arguments']];
                        },
                        $items
                    ),
                ]
            );
        }, $view->getContext());
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/teaser-list.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://libero.pub}item-list' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'list');
    }
}
