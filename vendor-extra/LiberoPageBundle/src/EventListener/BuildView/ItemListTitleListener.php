<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Libero\ViewsBundle\array_has_key;

final class ItemListTitleListener
{
    use ContextAwareTranslation;
    use SimplifiedViewConverterListener;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        if (!$view->hasContext('list_title')) {
            return $view;
        }

        $level = $view->getContext()['level'] ?? 1;

        return $view
            ->withArgument(
                'title',
                ['level' => $level, 'text' => $this->translate($view->getContext('list_title'), $view->getContext())]
            )
            ->withContext(['level' => $level + 1]);
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
        return !array_has_key($arguments, 'title');
    }
}
