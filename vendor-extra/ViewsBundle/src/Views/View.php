<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use JsonSerializable;

interface View extends JsonSerializable
{
    public function hasContext(string $key) : bool;

    public function getContext(?string $key = null);
}
