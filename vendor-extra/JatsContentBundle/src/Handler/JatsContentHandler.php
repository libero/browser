<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\Handler;

use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Handler\ContentHandler;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use UnexpectedValueException;
use function array_merge;

final class JatsContentHandler implements ContentHandler
{
    private $converter;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    public function handle(Element $documentElement, array $context) : array
    {
        /** @var Document $document */
        $document = $documentElement->ownerDocument;
        $xpath = $document->xpath();
        $xpath->registerNamespace('libero', 'http://libero.pub');
        $xpath->registerNamespace('jats', 'http://jats.nlm.nih.gov');

        $article = $xpath->firstOf('/libero:item/jats:article');

        if (!$article instanceof Element) {
            throw new UnexpectedValueException('Could not find an article');
        }

        if (!$article->hasAttribute('xml:lang')) {
            $article->setAttribute('xml:lang', 'en');
        }

        $front = $xpath->firstOf('jats:front', $article);

        if (!$front instanceof Element) {
            throw new UnexpectedValueException('Could not find a front');
        }

        $contentHeader = $this->converter->convert($front, '@LiberoPatterns/content-header.html.twig', $context);

        $content = [$contentHeader];

        $tags = $this->converter->convert($front, '@LiberoPatterns/item-tags.html.twig', $context);
        if ($tags->hasArgument('groups')) {
            $content[] = new View(
                '@LiberoPatterns/single-column-grid.html.twig',
                ['content' => [$tags]]
            );
        }

        return array_merge(
            $context,
            [
                'title' => $contentHeader->getArgument('contentTitle')['text'],
                'content' => $content,
            ]
        );
    }
}
