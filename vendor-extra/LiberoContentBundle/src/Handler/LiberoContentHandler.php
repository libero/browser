<?php

declare(strict_types=1);

namespace Libero\LiberoContentBundle\Handler;

use FluentDOM\DOM\Attribute;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Xpath;
use Libero\ContentPageBundle\Handler\ContentHandler;
use UnexpectedValueException;
use function array_merge;
use function Libero\ContentPageBundle\text_direction;

final class LiberoContentHandler implements ContentHandler
{
    public function handle(Element $documentElement, array $context) : array
    {
        /** @var Document $document */
        $document = $documentElement->ownerDocument;
        $xpath = $document->xpath();
        $xpath->registerNamespace('libero', 'http://libero.pub');

        $front = $xpath->firstOf('/libero:item/libero:front[1]');

        if (!$front instanceof Element) {
            throw new UnexpectedValueException('Could not find a front');
        }

        $title = $xpath->firstOf('libero:title', $front);

        if (!$title instanceof Element) {
            throw new UnexpectedValueException('Could not find a title');
        }

        $contentHeader = [
            'template' => '@LiberoPatterns/content-header.html.twig',
            'arguments' => [
                'attributes' => $this->determineLangAndDir($xpath, $front, $context),
                'contentTitle' => [
                    'attributes' => [],
                    'text' => (string) $title,
                ],
            ],
        ];

        return array_merge(
            $context,
            [
                'title' => $contentHeader['arguments']['contentTitle']['text'],
                'content' => [$contentHeader],
            ]
        );
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

            if ($context['dir'] !== $newDir = text_direction($return['lang'])) {
                $return['dir'] = $newDir;
            }
        }

        return $return;
    }
}
