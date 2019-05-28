<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use FluentDOM\DOM\CdataSection;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use FluentDOM\DOM\Text;

final class StringViewConverter implements ViewConverter
{
    private $fallback;

    public function __construct(ViewConverter $fallback)
    {
        $this->fallback = $fallback;
    }

    public function convert(NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : View
    {
        if (!$node instanceof Element && !$node instanceof Text && !$node instanceof CdataSection) {
            return $this->fallback->convert($node, $template, $context);
        }

        return new StringView((string) $node, $context);
    }
}
