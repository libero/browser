<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\ViewConverter;

use FluentDOM\DOM\Attribute;
use FluentDOM\DOM\Document;
use FluentDOM\DOM\Element;
use Punic\Misc;

trait LangAttributes
{
    final private function addLangAttribute(Element $element, array &$context, array $attributes = []) : array
    {
        $lang = $element->getAttributeNode('xml:lang');

        if (!$lang instanceof Attribute) {
            /** @var Document $document */
            $document = $element->ownerDocument;
            $lang = $document->xpath()->firstOf('ancestor::*[@xml:lang][1]/@xml:lang', $element);
        }

        if (!$lang instanceof Attribute || $lang->nodeValue === ($context['lang'] ?? null)) {
            return $attributes;
        }

        $context['lang'] = $lang->nodeValue;
        $attributes['lang'] = $context['lang'];
        $dir = 'right-to-left' === Misc::getCharacterOrder($context['lang']) ? 'rtl' : 'ltr';
        if (($context['dir'] ?? null) !== $dir) {
            $context['dir'] = $dir;
            $attributes['dir'] = $dir;
        }

        return $attributes;
    }
}
