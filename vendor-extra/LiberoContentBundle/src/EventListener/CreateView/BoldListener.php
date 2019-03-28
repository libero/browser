<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\EventListener\CreateView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\OptionalTemplateListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function Libero\ViewsBundle\array_has_key;

final class BoldListener
{
    use ConvertsChildren;
    use OptionalTemplateListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, View $view) : View
    {
        return $view->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function template() : string
    {
        return '@LiberoPatterns/bold.html.twig';
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://libero.pub}bold' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'text');
    }
}
