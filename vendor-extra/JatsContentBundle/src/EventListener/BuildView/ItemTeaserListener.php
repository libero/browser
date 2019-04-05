<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;

final class ItemTeaserListener
{
    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function onBuildView(BuildViewEvent $event) : void
    {
        $object = $event->getObject();
        $view = $event->getView();

        if (!$view instanceof TemplateView || !$this->canHandleTemplate($view->getTemplate())) {
            return;
        }

        if (!$this->canHandleElement(sprintf('{%s}%s', $object->namespaceURI, $object->localName))) {
            return;
        }

        $event->setView($this->handle($object, $view));

        $event->stopPropagation();
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        $front = $object->ownerDocument->xpath()
            ->firstOf(
                '/libero:item/jats:article/jats:front',
                $object
            );

        if (!$front instanceof Element) {
            return $view;
        }

        return $this->converter->convert($front, $view->getTemplate(), $view->getContext());
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/teaser.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://libero.pub}item' === $element;
    }
}
