<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

interface View
{
    public function hasContext(string $key) : bool;

    public function getContext(?string $key = null);
}
