<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\OptionalTemplateListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\ViewConverter;
use function Libero\ViewsBundle\array_has_key;

final class FigGraphicFigureListener
{
    use ConvertsChildren;
    use OptionalTemplateListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : TemplateView
    {
        /** @var DOMNodeList<Element> $graphics */
        $graphics = $object('jats:graphic');

        foreach ($graphics as $graphic) {
            $converted = $this->converter->convert($graphic, '@LiberoPatterns/image.html.twig', $view->getContext());

            if (!$converted instanceof TemplateView || !$converted->hasArgument('image')) {
                continue;
            }

            return $view->withArgument('content', $converted);
        }

        return $view;
    }

    protected function template() : string
    {
        return '@LiberoPatterns/figure.html.twig';
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://jats.nlm.nih.gov}fig' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'content');
    }
}
