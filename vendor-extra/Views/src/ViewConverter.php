<?php

declare(strict_types=1);

namespace Libero\Views;

use FluentDOM\DOM\Element;

interface ViewConverter
{
    public function convert(Element $object, string $template, array $context = []) : View;
}
