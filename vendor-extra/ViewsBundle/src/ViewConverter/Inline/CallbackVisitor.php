<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\ViewConverter\Inline;

use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\InlineViewConverterVisitor;
use Libero\ViewsBundle\Views\View;
use function call_user_func_array;

final class CallbackVisitor implements InlineViewConverterVisitor
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function visit(NonDocumentTypeChildNode $object, View $view, array &$context = []) : View
    {
        return call_user_func_array($this->callback, [$object, $view, &$context]);
    }
}
