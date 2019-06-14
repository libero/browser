<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use GuzzleHttp\Psr7\UriResolver;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use function GuzzleHttp\Psr7\uri_for;
use function in_array;
use function Libero\ViewsBundle\array_has_key;

final class FrontPdfContentHeaderListener
{
    use ViewBuildingListener;

    protected function handle(Element $object, TemplateView $view) : View
    {
        $pdf = $object->ownerDocument->xpath()
            ->firstOf("jats:article-meta/jats:self-uri[@content-type='pdf']", $object);

        if (!$pdf instanceof Element) {
            return $view;
        }

        $uri = UriResolver::resolve(
            uri_for($pdf->baseURI),
            uri_for($pdf->getAttributeNS('http://www.w3.org/1999/xlink', 'href'))
        );

        if (!in_array($uri->getScheme(), ['http', 'https'], true)) {
            return $view;
        }

        return $view->withArgument('downloadIconLink', ['attributes' => ['href' => (string) $uri]]);
    }

    protected function template() : string
    {
        return '@LiberoPatterns/content-header.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}front' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'downloadIconLink');
    }
}
