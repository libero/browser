<?php

declare(strict_types=1);

namespace Libero\ViewsBundle;

use function array_diff;
use function array_keys;
use function count;
use function in_array;

function array_has_key(array $array, string ...$keys) : bool
{
    return 0 === count(array_diff($keys, array_keys($array)));
}

function string_is(string $string, string ...$against) : bool
{
    return in_array($string, $against);
}
