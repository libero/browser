<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use function array_reduce;
use function Libero\ViewsBundle\array_has_key;

final class ContribLinkListener
{
    use ViewBuildingListener;

    protected function handle(Element $object, TemplateView $view) : View
    {
        $xpath = $object->ownerDocument->xpath();

        $name = $xpath->firstOf('jats:name', $object);

        if (!$name instanceof Element) {
            return $view;
        }

        switch ($name->getAttribute('name-style')) {
            case 'eastern':
                $order = [
                    'jats:prefix',
                    'jats:surname',
                    'jats:given-names',
                    'jats:suffix',
                ];
                break;
            case 'given-only':
                $order = [
                    'jats:prefix',
                    'jats:given-names',
                    'jats:suffix',
                ];
                break;
            case 'islensk':
            case 'western':
            default:
                $order = [
                    'jats:prefix',
                    'jats:given-names',
                    'jats:surname',
                    'jats:suffix',
                ];
        }

        $text = array_reduce(
            $order,
            static function (string $text, string $component) use ($name, $xpath) : string {
                $element = $xpath->firstOf($component, $name);

                if (!$element instanceof Element) {
                    return $text;
                }

                if ('' === $text) {
                    return $element->textContent;
                }

                return "{$text} {$element->textContent}";
            },
            ''
        );

        if ('' === $text) {
            return $view;
        }

        return $view->withArgument('text', $text);
    }

    protected function template() : string
    {
        return '@LiberoPatterns/link.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}contrib' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'text');
    }
}
