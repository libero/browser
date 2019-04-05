<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle;

use InvalidArgumentException;
use Punic\Exception\InvalidLocale;
use Punic\Misc;

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
