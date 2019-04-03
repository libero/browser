<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function Libero\ViewsBundle\array_has_key;

final class ItemArticleTitleTeaserListener
{
    use SimplifiedViewConverterListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, View $view) : View
    {
        $heading = $object->ownerDocument->xpath()
            ->firstOf(
                '/libero:item/jats:article/jats:front/jats:article-meta/jats:title-group/jats:article-title',
                $object
            );

        if (!$heading instanceof Element) {
            return $view;
        }

        return $view
            ->withArgument(
                'heading',
                $this->converter
                    ->convert($heading, '@LiberoPatterns/heading.html.twig', $view->getContext())
                    ->getArguments()
            );
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/teaser.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://libero.pub}item' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'heading');
    }
}
