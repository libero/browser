<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use DOMNodeList;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use Symfony\Contracts\Translation\TranslatorInterface;
use function array_map;
use function count;
use function iterator_to_array;
use function Libero\ContentPageBundle\translation_key;

final class KeywordGroupTagListVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;

    private $converter;
    private $translator;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator)
    {
        $this->converter = $converter;
        $this->translator = $translator;
    }

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        /** @var Document $document */
        $document = $object->ownerDocument;
        $xpath = $document->xpath();
        $xpath->registerNamespace('jats', 'http://jats.nlm.nih.gov');

        $title = $xpath->firstOf('jats:title', $object);

        /** @var DOMNodeList|Element[] $keywords */
        $keywords = $object('jats:kwd');

        if (0 === count($keywords)) {
            return $view;
        }

        if ($title instanceof Element) {
            $view = $view->withArgument(
                'title',
                $this->converter->convert($title, '@LiberoPatterns/heading.html.twig', $context)->getArguments()
            );
        } else {
            $view = $view->withArgument(
                'title',
                [
                    'text' => $this->translator->trans(
                        translation_key(
                            'libero.jats.keyword_group.title.%s',
                            $object->getAttribute('kwd-group-type')
                        )
                    ),
                ]
            );
        }

        return $view->withArgument(
            'list',
            [
                'items' => array_map(
                    function (Element $keyword) use ($context) {
                        return [
                            'content' => $this->converter->convert(
                                $keyword,
                                '@LiberoPatterns/link.html.twig',
                                $context
                            )->getArguments(),
                        ];
                    },
                    iterator_to_array($keywords)
                ),
            ]
        );
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/tag-list.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://jats.nlm.nih.gov}kwd-group';
    }

    protected function unexpectedArguments() : array
    {
        return ['list'];
    }
}
