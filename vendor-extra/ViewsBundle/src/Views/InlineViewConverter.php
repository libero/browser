<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Node\NonDocumentTypeChildNode;

interface InlineViewConverter
{
    public function convert(NonDocumentTypeChildNode $object, array $context = []) : View;
}
