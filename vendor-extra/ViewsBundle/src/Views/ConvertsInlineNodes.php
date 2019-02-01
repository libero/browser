<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use function array_map;
use function iterator_to_array;

trait ConvertsInlineNodes
{
    /** @var InlineViewConverter */
    private $inlineConverter;

    final protected function convertInlineNodes(Element $object, array $context = []) : array
    {
        return array_map(
            function (NonDocumentTypeChildNode $child) use ($context) : View {
                return $this->inlineConverter->convert($child, $context);
            },
            iterator_to_array($object)
        );
    }
}
