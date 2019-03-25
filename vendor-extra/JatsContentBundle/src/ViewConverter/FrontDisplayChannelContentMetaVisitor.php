<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\TranslatingVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FrontDisplayChannelContentMetaVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;
    use TranslatingVisitor;

    private const CATEGORIES_PATH = 'jats:article-meta/jats:article-categories';
    private const GROUP_PATH = self::CATEGORIES_PATH.'/jats:subj-group[@subj-group-type="display-channel"]';
    private const SUBJECT_PATH = self::GROUP_PATH.'/jats:subject';

    private $converter;
    private $translator;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator)
    {
        $this->converter = $converter;
        $this->translator = $translator;
    }

    protected function doVisit(Element $object, View $view) : View
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
