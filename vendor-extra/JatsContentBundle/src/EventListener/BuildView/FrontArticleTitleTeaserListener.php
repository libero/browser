<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function Libero\ViewsBundle\array_has_key;

final class FrontArticleTitleTeaserListener
{
    use ViewBuildingListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        $heading = $object->ownerDocument->xpath()
            ->firstOf(
                'jats:article-meta/jats:title-group/jats:article-title',
                $object
            );

        if (!$heading instanceof Element) {
            return $view;
        }

        $heading = $this->converter->convert($heading, '@LiberoPatterns/heading.html.twig', $view->getContext());

        if (!$heading instanceof TemplateView) {
            return $view->withArgument('heading', $heading);
        }

        return $view->withArgument('heading', $heading->getArguments());
    }

    protected function template() : string
    {
        return '@LiberoPatterns/teaser.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}front' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'heading');
    }
}
