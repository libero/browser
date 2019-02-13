<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use DOMNodeList;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedChildVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use function array_map;
use function iterator_to_array;

final class SectionVisitor implements ViewConverterVisitor
{
    use ConvertsChildren;
    use SimplifiedChildVisitor;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        $context['level'] = $context['level'] ?? 1;

        /** @var Document $document */
        $document = $object->ownerDocument;

        $heading = $document->xpath()->firstOf('jats:title', $object);

        if ($heading instanceof Element) {
            $view = $view->withArgument(
                'heading',
                $this->converter->convert($heading, '@LiberoPatterns/heading.html.twig', $context)->getArguments()
            );
        }

        /** @var DOMNodeList|Element[] $children */
        $children = $object('*[not(local-name()="title" and namespace-uri()="http://jats.nlm.nih.gov")]');

        $childContext = $context;
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

    protected function possibleTemplate() : string
    {
        return '@LiberoPatterns/section.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://jats.nlm.nih.gov}sec';
    }

    protected function unexpectedArguments() : array
    {
        return ['content'];
    }
}
