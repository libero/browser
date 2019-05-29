<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function Libero\ViewsBundle\array_has_key;

final class FrontContentHeaderListener
{
    use ViewBuildingListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        $title = $object->ownerDocument->xpath()
            ->firstOf('libero:title[1]', $object);

        if (!$title instanceof Element) {
            return $view;
        }

        return $view->withArgument(
            'contentTitle',
            $this->converter
                ->convert($title, '@LiberoPatterns/heading.html.twig', $view->getContext())
                ->getArguments()
        );
    }

    protected function template() : string
    {
        return '@LiberoPatterns/content-header.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://libero.pub}front' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'contentTitle');
    }
}
