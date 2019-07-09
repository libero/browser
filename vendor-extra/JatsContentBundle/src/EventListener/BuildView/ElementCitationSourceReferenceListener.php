<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function Libero\ViewsBundle\array_has_key;

final class ElementCitationSourceReferenceListener
{
    use ViewBuildingListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        $source = $object->ownerDocument->xpath()
            ->firstOf('jats:source', $object);

        if (!$source instanceof Element) {
            return $view;
        }

        return $view->withArgument(
            'details',
            ['text' => $this->converter->convert($source, '@LiberoPatterns/italic.html.twig', $view->getContext())]
        );
    }

    protected function template() : string
    {
        return '@LiberoPatterns/reference.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}element-citation' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'details');
    }
}
