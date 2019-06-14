<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use function array_map;
use function iterator_to_array;

trait ConvertsChildren
{
    /** @var ViewConverter */
    private $converter;

    final protected function convertChildren(Element $object, array $context = []) : array
    {
        if ($context['strip_inline'] ?? false) {
            return [new StringView((string) $object, $context)];
        }

        return array_map(
            function (NonDocumentTypeChildNode $child) use ($context) : View {
                return $this->converter->convert($child, null, $context);
            },
            iterator_to_array($object)
        );
    }
}
