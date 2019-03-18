<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;

interface ViewConverterVisitor
{
    public function visit(Element $object, View $view) : View;
}
