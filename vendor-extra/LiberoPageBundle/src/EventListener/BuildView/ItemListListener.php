<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use ArrayAccess;
use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\LazyView;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function array_map;
use function count;

final class ItemListListener
{
    use ContextAwareTranslation;
    use SimplifiedViewConverterListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        /** @var DOMNodeList<Element> $itemRefs */
        $itemRefs = $object('libero:item-ref');

        if (0 === count($itemRefs)) {
            return $view;
        }

        $context = $view->getContext();
        unset($context['list_empty']);

        $items = [];
        foreach ($itemRefs as $itemRef) {
            $items[] = $this->converter->convert($itemRef, '@LiberoPatterns/teaser.html.twig', $context);
        }

        return new LazyView(
            function () use ($view, $items) {
                $list = $view->getArgument('list') ?? [];
                $list['items'] = array_map(
                    function (ArrayAccess $view) {
                        return ['content' => $view['arguments']];
                    },
                    $items
                );

                return $view->withArgument('list', $list);
            },
            $context
        );
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
        return !isset($arguments['list']['items']);
    }
}
