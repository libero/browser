<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Punic\Misc;

trait LangAttributes
{
    final private function addLangAttribute(Element $element, array &$context, array $attributes = []) : array
    {
        $lang = $element->getAttribute('xml:lang');

        if (!$lang || $lang === ($context['lang'] ?? null)) {
            return $attributes;
        }
        $context['lang'] = $lang;
        $attributes['lang'] = $context['lang'];
        $dir = 'right-to-left' === Misc::getCharacterOrder($context['lang']) ? 'rtl' : 'ltr';
        if (($context['dir'] ?? null) !== $dir) {
            $context['dir'] = $dir;
            $attributes['dir'] = $dir;
        }

        return $attributes;
    }
}
