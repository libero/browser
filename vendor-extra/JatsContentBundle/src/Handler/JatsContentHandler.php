<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\Handler;

use FluentDOM\DOM\Attribute;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Xpath;
use Libero\ContentPageBundle\Handler\ContentHandler;
use Punic\Misc;
use UnexpectedValueException;
use function array_merge;

final class JatsContentHandler implements ContentHandler
{
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
