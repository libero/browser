<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function Libero\ViewsBundle\array_has_key;

final class FrontArticleTitleContentHeaderListener
{
    use SimplifiedViewConverterListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, View $view) : View
    {
        $title = $object->ownerDocument->xpath()
            ->firstOf('jats:article-meta/jats:title-group/jats:article-title', $object);

        if (!$title instanceof Element) {
            return $view;
        }

        return $view->withArgument(
            'contentTitle',
            $this->converter->convert($title, '@LiberoPatterns/heading.html.twig', $view->getContext())->getArguments()
        );
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/content-header.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://jats.nlm.nih.gov}front' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'contentTitle');
    }
}
