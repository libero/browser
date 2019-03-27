<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\CreateView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Event\CreateViewEvent;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedChildViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function array_map;
use function iterator_to_array;

final class SectionListener
{
    use ConvertsChildren;
    use SimplifiedChildViewConverterListener;

    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(CreateViewEvent $event) : View
    {
        $object = $event->getObject();
        $view = $event->getView();

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

        /** @var DOMNodeList|Element[] $children */
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
