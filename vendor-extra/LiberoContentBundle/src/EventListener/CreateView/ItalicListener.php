<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\EventListener\CreateView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;

final class ItalicListener
{
    use ConvertsChildren;
    use SimplifiedViewConverterListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, View $view) : View
    {
        return $view->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function possibleTemplate() : string
    {
        return '@LiberoPatterns/italic.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://libero.pub}italic'];
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
