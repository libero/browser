<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use DOMNodeList;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\TranslatingVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use Symfony\Contracts\Translation\TranslatorInterface;
use function array_map;
use function count;

final class KeywordGroupTagListVisitor implements ViewConverterVisitor
{
    private const DEFAULT_TRANSLATION_KEY = 'libero.jats.keyword_group.title.default';
    private const TRANSLATION_KEYS = [
        'author-keywords' => 'libero.jats.keyword_group.title.author_keywords',
        'research-organism' => 'libero.jats.keyword_group.title.research_organism',
    ];

    use ConvertsLists;
    use SimplifiedVisitor;
    use TranslatingVisitor;

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
            $title = $this->converter->convert($title, '@LiberoPatterns/heading.html.twig', $context)->getArguments();
        } else {
            $title = ['text' => $this->translate($this->translationKey($object), $context)];
        }

        return $view
            ->withArgument('title', $title)
            ->withArgument(
                'list',
                [
                    'items' => array_map(
                        function (View $link) : array {
                            return ['content' => $link->getArguments()];
                        },
                        $this->convertList($keywords, '@LiberoPatterns/link.html.twig', $context)
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

    private function translationKey(Element $element) : string
    {
        return self::TRANSLATION_KEYS[$element->getAttribute('kwd-group-type')] ?? self::DEFAULT_TRANSLATION_KEY;
    }
}
