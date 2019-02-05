<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Node\NonDocumentTypeChildNode;

interface ViewConverter
{
    public function convert(NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : View;
}
