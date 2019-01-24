<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle;

use Punic\Misc;

const DIRECTION_LTR = 'ltr';
const DIRECTION_RTL = 'rtl';

function text_direction(string $locale) : string
{
    return 'right-to-left' === Misc::getCharacterOrder($locale) ? DIRECTION_RTL : DIRECTION_LTR;
}
