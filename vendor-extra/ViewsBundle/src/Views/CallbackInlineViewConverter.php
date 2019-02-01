<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use function array_map;
use function call_user_func;
use function iterator_to_array;

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

    public function convertChildren(Element $object, array $context = []) : array
    {
        return array_map(
            function (NonDocumentTypeChildNode $child) use ($context) : View {
                return $this->convert($child, $context);
            },
            iterator_to_array($object)
        );
    }
}
