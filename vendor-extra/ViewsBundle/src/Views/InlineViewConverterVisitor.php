<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Node\NonDocumentTypeChildNode;

interface InlineViewConverterVisitor
{
    public function visit(NonDocumentTypeChildNode $object, View $view, array &$context = []) : View;
}
