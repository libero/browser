<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use JsonSerializable;
use Traversable;

interface View extends JsonSerializable, Traversable
{
    public function hasContext(string $key) : bool;

    public function getContext(?string $key = null);
}
