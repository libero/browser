<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\TemplateChoosingListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function array_pop;
use function array_reduce;
use function end;
use function in_array;
use function Libero\ViewsBundle\array_has_key;

trait NameListener
{
    use TemplateChoosingListener;
    use ViewBuildingListener;

    /** @var ViewConverter */
    private $converter;

    final protected function handle(Element $object, TemplateView $view) : View
    {
        $xpath = $object->ownerDocument->xpath();

        $text = array_reduce(
            $this->nameOrder(),
            function (array $text, string $localName) use ($object, $view, $xpath) : array {
                $element = $xpath->firstOf("jats:{$localName}", $object);

                if (!$element instanceof Element) {
                    return $text;
                }

                $part = $this->converter->convert($element, $view->getTemplate(), $view->getContext());

                $spaces = $this->addSpaces($part->getContext('lang') ?? 'und');

                if ($spaces && ' ' !== (end($text) ?: ' ')) {
                    $text[] = ' ';
                }
                $text[] = $part;
                if ($spaces) {
                    $text[] = ' ';
                }

                return $text;
            },
            []
        );

        if (' ' === end($text)) {
            array_pop($text);
        }

        return $view->withArgument('text', $text);
    }

    final protected function template() : string
    {
        return '@LiberoPatterns/link.html.twig';
    }

    final protected function canHandleElement(Element $element) : bool
    {
        if ('{http://jats.nlm.nih.gov}name' !== $element->clarkNotation()) {
            return false;
        }

        return in_array($element->getAttribute('name-style'), $this->nameStyles(), true);
    }

    final protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'text');
    }

    /**
     * @return array<string>
     */
    abstract protected function nameOrder() : array;

    /**
     * @return array<null|string>
     */
    abstract protected function nameStyles() : array;

    protected function addSpaces(string $lang) : bool
    {
        return true;
    }
}
