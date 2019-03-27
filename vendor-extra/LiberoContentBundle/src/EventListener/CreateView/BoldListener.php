<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\EventListener\CreateView;

use Libero\ViewsBundle\Event\CreateViewEvent;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedChildViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;

final class BoldListener
{
    use ConvertsChildren;
    use SimplifiedChildViewConverterListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(CreateViewEvent $event) : View
    {
        $object = $event->getObject();
        $view = $event->getView();

        return $view->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function possibleTemplate() : string
    {
        return '@LiberoPatterns/bold.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://libero.pub}bold';
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
