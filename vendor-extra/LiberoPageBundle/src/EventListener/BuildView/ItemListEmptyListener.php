<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ItemListEmptyListener
{
    use ContextAwareTranslation;
    use ViewBuildingListener;

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

    protected function template() : string
    {
        return '@LiberoPatterns/teaser-list.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://libero.pub}item-list' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !isset($arguments['list']['empty']);
    }
}
