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
use Symfony\Contracts\Translation\TranslatorInterface;
use function array_filter;
use function array_map;
use function Libero\ViewsBundle\array_has_key;

final class ItemListListener
{
    use ContextAwareTranslation;
    use SimplifiedViewConverterListener;

    private $converter;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator)
    {
        $this->converter = $converter;
        $this->translator = $translator;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        /** @var DOMNodeList<Element> $itemRefs */
        $itemRefs = $object('libero:item-ref');

        $level = ($view->getContext()['level'] ?? 1) + 1;

        $items = [];
        foreach ($itemRefs as $itemRef) {
            $items[] = $this->converter->convert(
                $itemRef,
                '@LiberoPatterns/teaser.html.twig',
                ['level' => $level + 1] + $view->getContext()
            );
        }

        return new LazyView(
            function () use ($level, $view, $items) {
                if ($view->hasContext('list_title')) {
                    $view = $view->withArgument(
                        'title',
                        [
                            'level' => $level,
                            'text' => $this->translate($view->getContext('list_title'), $view->getContext()),
                        ]
                    );
                }

                return $view->withArgument(
                    'list',
                    [
                        'items' => array_filter(
                            array_map(
                                function (View $view) {
                                    if (!$view instanceof ArrayAccess) {
                                        return [];
                                    }

                                    return ['content' => $view['arguments']];
                                },
                                $items
                            )
                        ),
                    ]
                );
            },
            $view->getContext()
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
        return !array_has_key($arguments, 'list');
    }
}
