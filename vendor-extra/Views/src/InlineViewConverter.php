<?php

declare(strict_types=1);

namespace Libero\Views;

use FluentDOM\DOM\Node\ChildNode;

interface InlineViewConverter
{
    public function convert(ChildNode $object, array $context = []) : View;
}
