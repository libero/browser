<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FrontDisplayChannelContentMetaListener
{
    use ContextAwareTranslation;
    use SimplifiedViewConverterListener;

    private const CATEGORIES_PATH = 'jats:article-meta/jats:article-categories';
    private const GROUP_PATH = self::CATEGORIES_PATH.'/jats:subj-group[@subj-group-type="display-channel"]';
    private const SUBJECT_PATH = self::GROUP_PATH.'/jats:subject';

    private $converter;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator)
    {
        $this->converter = $converter;
        $this->translator = $translator;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        $items = $view->getArgument('items') ?? [];

        if (isset($items['type'])) {
            return $view;
        }

        $displayChannel = $object->ownerDocument->xpath()->firstOf(self::SUBJECT_PATH, $object);

        if (!$displayChannel instanceof Element) {
            return $view;
        }

        $items['type'] = [
            'attributes' => [
                'aria-label' => $this->translate('libero.patterns.content_header.meta.type.label', $view->getContext()),
            ],
            'content' => $this->converter->convert(
                $displayChannel,
                '@LiberoPatterns/link.html.twig',
                $view->getContext()
            )->getArguments(),
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
