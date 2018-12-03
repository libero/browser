<?php

declare(strict_types=1);

namespace Libero\Views;

use FluentDOM\DOM\Element;

interface ViewConverterVisitor
{
    public function visit(Element $object, View $view, array &$context = []) : View;
}
