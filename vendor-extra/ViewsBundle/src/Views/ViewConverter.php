<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Node;

interface ViewConverter
{
    public function convert(Node $node, ?string $template = null, array $context = []) : View;
}
