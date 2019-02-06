<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle;

use InvalidArgumentException;
use Punic\Exception\InvalidLocale;
use Punic\Misc;
use function array_map;
use function sprintf;
use function str_replace;
use function strtolower;

const DIRECTION_LTR = 'ltr';
const DIRECTION_RTL = 'rtl';

function text_direction(string $locale) : string
{
    try {
        return 'right-to-left' === Misc::getCharacterOrder($locale) ? DIRECTION_RTL : DIRECTION_LTR;
    } catch (InvalidLocale $exception) {
        throw new InvalidArgumentException($exception->getMessage(), 0, $exception);
    }
}

function translation_key(string $text, ...$arguments) : string
{
    return sprintf($text, ...array_map('Libero\ContentPageBundle\normalize_translation_key_part', $arguments));
}

/**
 * @internal
 */
function normalize_translation_key_part($part) : string
{
    return strtolower(str_replace('-', '_', (string) $part));
}
