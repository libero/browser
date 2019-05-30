<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function Libero\ViewsBundle\array_has_key;
use function Libero\ViewsBundle\string_is;

final class HeadingListener
{
    use ConvertsChildren;
    use ViewBuildingListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        if ($view->hasContext('level')) {
            $view = $view->withArgument('level', $view->getContext('level'));
        }

        return $view->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function template() : string
    {
        return '@LiberoPatterns/heading.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return string_is(
            $element->clarkNotation(),
            '{http://jats.nlm.nih.gov}article-title',
            '{http://jats.nlm.nih.gov}title'
        );
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'text');
    }
}
