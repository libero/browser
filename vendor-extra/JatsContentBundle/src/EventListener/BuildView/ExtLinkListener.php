<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use GuzzleHttp\Psr7\UriResolver;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\TemplateChoosingListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function GuzzleHttp\Psr7\uri_for;
use function Libero\ViewsBundle\array_has_key;

final class ExtLinkListener
{
    use ConvertsChildren;
    use TemplateChoosingListener;
    use ViewBuildingListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : TemplateView
    {
        $uri = UriResolver::resolve(
            uri_for($object->baseURI),
            uri_for($object->getAttributeNS('http://www.w3.org/1999/xlink', 'href'))
        );

        if ('' === $uri->getScheme()) {
            return $view;
        }

        $attributes = $view->getArgument('attributes') ?? [];
        $attributes['href'] = (string) $uri;

        return $view
            ->withArgument('attributes', $attributes)
            ->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function template() : string
    {
        return '@LiberoPatterns/link.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}ext-link' === $element->clarkNotation() &&
            $element->hasAttributeNS('http://www.w3.org/1999/xlink', 'href');
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'text');
    }
}
