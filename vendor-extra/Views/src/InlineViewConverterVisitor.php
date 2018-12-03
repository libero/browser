<?php

declare(strict_types=1);

namespace Libero\Views;

use FluentDOM\DOM\Node\ChildNode;

interface InlineViewConverterVisitor
{
    public function visit(ChildNode $object, View $view, array &$context = []) : View;
}
