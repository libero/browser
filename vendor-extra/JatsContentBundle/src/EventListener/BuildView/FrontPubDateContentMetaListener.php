<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FrontPubDateContentMetaListener
{
    use ContextAwareTranslation;
    use ViewBuildingListener;

    private const DATE_PATH = 'jats:article-meta/jats:pub-date[@date-type="pub"]';

    private $converter;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator)
    {
        $this->converter = $converter;
        $this->translator = $translator;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        $items = $view->getArgument('items') ?? [];

        if (isset($items['date'])) {
            return $view;
        }

        $pubDate = $object->ownerDocument->xpath()->firstOf(self::DATE_PATH, $object);

        if (!$pubDate instanceof Element) {
            return $view;
        }

        $date = $this->converter->convert($pubDate, '@LiberoPatterns/time.html.twig', $view->getContext());

        if (!$date instanceof TemplateView || empty($date->getArguments())) {
            return $view;
        }

        $items['date'] = [
            'attributes' => [
                'aria-label' => $this->translate('libero.patterns.content_header.meta.date.label', $view->getContext()),
            ],
            'content' => [
                'text' => [$date->withArgument('format', 'medium')],
            ],
        ];

        return $view->withArgument('items', $items);
    }

    protected function template() : string
    {
        return '@LiberoPatterns/content-meta.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}front' === $element->clarkNotation();
    }
}
