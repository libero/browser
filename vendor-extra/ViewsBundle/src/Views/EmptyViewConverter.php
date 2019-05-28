<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Node\NonDocumentTypeChildNode;

final class EmptyViewConverter implements ViewConverter
{
    public function convert(NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : View
    {
        return new EmptyView($context);
    }
}
