<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\TemplateChoosingListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;

final class FigCaptionTitleFigureListener
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
        $title = $object->ownerDocument->xpath()
            ->firstOf('jats:caption/jats:title', $object);

        if (!$title instanceof Element) {
            return $view;
        }

        $heading = $this->converter->convert($title, '@LiberoPatterns/heading.html.twig', $view->getContext());

        if (!$heading instanceof TemplateView) {
            return $view;
        }

        $caption = $view->getArgument('caption') ?? [];
        $caption['heading'] = $heading->getArguments();

        return $view->withContext(['level' => ($view->getContext('level') ?? 1) + 1])
            ->withArgument('caption', $caption);
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
        return !isset($arguments['caption']['heading']);
    }
}
