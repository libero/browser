<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\ViewConverter\Inline;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\LiberoPatternsBundle\ViewConverter\LangAttributes;
use Libero\ViewsBundle\Views\InlineViewConverterVisitor;
use Libero\ViewsBundle\Views\View;

final class LangVisitor implements InlineViewConverterVisitor
{
    use LangAttributes;

    public function visit(NonDocumentTypeChildNode $object, View $view, array &$context = []) : View
    {
        if (!$object instanceof Element) {
            return $view;
        }

        if ($view->hasArgument('attributes') && !empty($view->getArgument('attributes')['lang'])) {
            return $view;
        }

        return $view->withArgument(
            'attributes',
            $this->addLangAttribute(
                $object,
                $context,
                $view->getArgument('attributes') ?? []
            )
        );
    }
}
