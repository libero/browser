<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use Traversable;
use function array_map;
use function iterator_to_array;

trait ConvertsLists
{
    /** @var ViewConverter */
    private $converter;

    /**
     * @param iterable<Element> $objects
     */
    final protected function convertList(iterable $objects, ?string $template = null, array $context = []) : array
    {
        return array_map(
            function (Element $object) use ($context, $template) : View {
                return $this->converter->convert($object, $template, $context);
            },
            $objects instanceof Traversable ? iterator_to_array($objects) : $objects
        );
    }
}
