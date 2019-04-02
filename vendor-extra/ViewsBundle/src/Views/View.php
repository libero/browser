<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use ArrayAccess;
use JsonSerializable;

interface View extends ArrayAccess, JsonSerializable
{
    public function hasContext(string $key) : bool;

    public function getContext(?string $key = null);
}
