<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle;

use InvalidArgumentException;
use Punic\Exception\InvalidLocale;
use Punic\Misc;
use function array_map;
use function is_string;
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
    $arguments = array_map(
        function ($argument) {
            if (!is_string($argument)) {
                return $argument;
            }

            return strtolower(str_replace('-', '_', $argument));
        },
        $arguments
    );

    return sprintf($text, ...$arguments);
}
