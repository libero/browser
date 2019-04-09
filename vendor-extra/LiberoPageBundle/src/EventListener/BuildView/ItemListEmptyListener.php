<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ItemListEmptyListener
{
    use ContextAwareTranslation;
    use SimplifiedViewConverterListener;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        if (!$view->hasContext('list_empty')) {
            return $view;
        }

        return $view->withArgument(
            'list',
            ['empty' => $this->translate($view->getContext('list_empty'), $view->getContext())]
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
        return !isset($arguments['list']['empty']);
    }
}
