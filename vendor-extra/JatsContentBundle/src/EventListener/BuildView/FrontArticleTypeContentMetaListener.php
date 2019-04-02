<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Contracts\Translation\TranslatorInterface;
use function sprintf;

final class FrontArticleTypeContentMetaListener
{
    use ContextAwareTranslation;
    use SimplifiedViewConverterListener;

    private $translationKeys;

    public function __construct(TranslatorInterface $translator, array $translationKeys = [])
    {
        $this->translator = $translator;
        $this->translationKeys = $translationKeys;
    }

    protected function handle(Element $object, TemplateView $view) : TemplateView
    {
        $items = $view->getArgument('items') ?? [];

        if (isset($items['type'])) {
            return $view;
        }

        $article = $object->parentNode;

        if (!$article instanceof Element
            ||
            '{http://jats.nlm.nih.gov}article' !== sprintf('{%s}%s', $article->namespaceURI, $article->localName)
        ) {
            return $view;
        }

        $type = $article->getAttribute('article-type');

        if (!isset($this->translationKeys[$type])) {
            return $view;
        }

        $items['type'] = [
            'attributes' => [
                'aria-label' => $this->translate('libero.patterns.content_header.meta.type.label', $view->getContext()),
            ],
            'content' => [
                'text' => $this->translate($this->translationKeys[$type], $view->getContext()),
            ],
        ];

        return $view->withArgument('items', $items);
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/content-meta.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://jats.nlm.nih.gov}front' === $element;
    }
}
