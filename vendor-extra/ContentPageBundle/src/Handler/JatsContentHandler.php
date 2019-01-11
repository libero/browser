<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Handler;

use FluentDOM\DOM\Attribute;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Xpath;
use Punic\Misc;
use UnexpectedValueException;
use function array_merge;

final class JatsContentHandler implements ContentHandler
{
    public function handle(Element $documentElement, array $context) : array
    {
        if ('article' !== $documentElement->localName || 'http://jats.nlm.nih.gov' !== $documentElement->namespaceURI) {
            return [];
        }

        /** @var Document $document */
        $document = $documentElement->ownerDocument;
        $xpath = $document->xpath();
        $xpath->registerNamespace('jats', 'http://jats.nlm.nih.gov');

        $front = $xpath->firstOf('/jats:article/jats:front');

        if (!$front instanceof Element) {
            throw new UnexpectedValueException('Could not find a front');
        }

        $title = $xpath->firstOf('jats:article-meta/jats:title-group/jats:article-title', $front);

        if (!$title instanceof Element) {
            throw new UnexpectedValueException('Could not find a title');
        }

        $contentHeader = [
            'template' => '@LiberoPatterns/content-header.html.twig',
            'arguments' => [
                'attributes' => [],
                'contentTitle' => [
                    'attributes' => [],
                    'text' => (string) $title,
                ],
            ],
        ];

        $contentHeader['arguments']['attributes'] += $this->determineLangAndDir($xpath, $front, $context);
        $contentHeader['arguments']['contentTitle']['attributes'] += $this->determineLangAndDir(
            $xpath,
            $title,
            array_merge(
                $context,
                $contentHeader['arguments']['attributes']
            )
        );

        return array_merge(
            $context,
            [
                'title' => $contentHeader['arguments']['contentTitle']['text'],
                'content' => [$contentHeader],
            ]
        );
    }

    private function getDirection(?string $locale) : string
    {
        return 'right-to-left' === Misc::getCharacterOrder($locale ?? 'en') ? 'rtl' : 'ltr';
    }

    private function determineLangAndDir(Xpath $xpath, Element $element, array $context) : array
    {
        $return = [];

        $newLang = $xpath->firstOf('ancestor-or-self::*[@xml:lang][1]/@xml:lang', $element);

        if (!$newLang instanceof Attribute) {
            return $return;
        }

        if ($context['lang'] !== $newLang->nodeValue) {
            $return['lang'] = $newLang->nodeValue;

            if ($context['dir'] !== $newDir = $this->getDirection($return['lang'])) {
                $return['dir'] = $newDir;
            }
        }

        return $return;
    }
}
