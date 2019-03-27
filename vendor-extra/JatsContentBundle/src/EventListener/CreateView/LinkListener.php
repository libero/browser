<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\CreateView;

use Libero\ViewsBundle\Event\CreateViewEvent;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;

final class LinkListener
{
    use ConvertsChildren;
    use SimplifiedViewConverterListener;

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

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/link.html.twig';
    }

    protected function expectedElement() : array
    {
        return [
            '{http://jats.nlm.nih.gov}kwd',
            '{http://jats.nlm.nih.gov}subject',
        ];
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
