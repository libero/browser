<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use function call_user_func;

final class CallbackInlineViewConverter implements InlineViewConverter
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function convert(NonDocumentTypeChildNode $object, array $context = []) : View
    {
        return call_user_func($this->callback, $object, $context);
    }
}
