<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function array_merge;
use function array_reduce;
use function count;
use function Libero\ViewsBundle\array_has_key;

final class ContribLinkListener
{
    use ConvertsChildren;
    use ViewBuildingListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

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
            function (array $parts, string $component) use ($name, $view, $xpath) : array {
                $element = $xpath->firstOf($component, $name);

                if (!$element instanceof Element) {
                    return $parts;
                }

                if (0 !== count($parts)) {
                    $parts[] = ' ';
                }

                return array_merge($parts, $this->convertChildren($element, $view->getContext()));
            },
            []
        );

        if (0 === count($text)) {
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
