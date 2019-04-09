<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function sprintf;

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

        $handled = $this->handle($object, $view);

        if (!$handled instanceof View) {
            return;
        }

        $event->setView($handled);

        $event->stopPropagation();
    }

    protected function handle(Element $object, TemplateView $view) : ?View
    {
        $front = $object->ownerDocument->xpath()
            ->firstOf(
                '/libero:item/libero:front',
                $object
            );

        if (!$front instanceof Element) {
            return null;
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
