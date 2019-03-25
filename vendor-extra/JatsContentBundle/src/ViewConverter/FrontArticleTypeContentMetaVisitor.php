<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\TranslatingVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use Symfony\Contracts\Translation\TranslatorInterface;
use function sprintf;

final class FrontArticleTypeContentMetaVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;
    use TranslatingVisitor;

    private $translationKeys;

    public function __construct(TranslatorInterface $translator, array $translationKeys = [])
    {
        $this->translator = $translator;
        $this->translationKeys = $translationKeys;
    }

    protected function doVisit(Element $object, View $view) : View
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

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/content-meta.html.twig';
    }

    protected function expectedElement() : array
    {
        return [
            '{http://jats.nlm.nih.gov}front',
        ];
    }
}
