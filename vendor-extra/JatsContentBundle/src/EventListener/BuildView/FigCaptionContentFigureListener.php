<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\TemplateChoosingListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function array_map;
use function count;
use function iterator_to_array;

final class FigCaptionContentFigureListener
{
    use ConvertsChildren;
    use TemplateChoosingListener;
    use ViewBuildingListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : TemplateView
    {
        /** @var DOMNodeList<Element> $paragraphs */
        $paragraphs = $object('jats:caption/jats:p');

        if (0 === count($paragraphs)) {
            return $view;
        }

        $caption = $view->getArgument('caption') ?? [];
        $caption['content'] = array_map(
            function (Element $paragraph) use ($view) : View {
                return $this->converter->convert($paragraph, null, $view->getContext());
            },
            iterator_to_array($paragraphs)
        );

        return $view->withArgument('caption', $caption);
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
        return !isset($arguments['caption']['content']);
    }
}
