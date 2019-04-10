<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\OptionalTemplateListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function array_map;
use function iterator_to_array;
use function Libero\ViewsBundle\array_has_key;

final class SectionListener
{
    use ConvertsChildren;
    use OptionalTemplateListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : TemplateView
    {
        if (!$view->hasContext('level')) {
            $view = $view->withContext(['level' => 1]);
        }

        $heading = $object->ownerDocument->xpath()
            ->firstOf('jats:title', $object);

        if ($heading instanceof Element) {
            $view = $view->withArgument(
                'heading',
                $this->converter
                    ->convert($heading, '@LiberoPatterns/heading.html.twig', $view->getContext())
                    ->getArguments()
            );
        }

        /** @var DOMNodeList<Element> $children */
        $children = $object('*[not(local-name()="title" and namespace-uri()="http://jats.nlm.nih.gov")]');

        $childContext = $view->getContext();
        $childContext['level']++;

        return $view->withArgument(
            'content',
            array_map(
                function (NonDocumentTypeChildNode $child) use ($childContext) : View {
                    return $this->converter->convert($child, null, $childContext);
                },
                iterator_to_array($children)
            )
        );
    }

    protected function template() : string
    {
        return '@LiberoPatterns/section.html.twig';
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://jats.nlm.nih.gov}sec' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'content');
    }
}
