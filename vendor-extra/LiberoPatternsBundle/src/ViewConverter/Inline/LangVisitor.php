<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\ViewConverter\Inline;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\InlineViewConverterVisitor;
use Libero\ViewsBundle\Views\LangAttributes;
use Libero\ViewsBundle\Views\View;

final class LangVisitor implements InlineViewConverterVisitor
{
    use LangAttributes;

    public function visit(NonDocumentTypeChildNode $object, View $view, array &$context = []) : View
    {
        if (!$object instanceof Element) {
            return $view;
        }

        if (!empty($view->getArgument('attributes')['lang'])) {
            return $view;
        }

        $lang = $object->getAttribute('xml:lang');
        if (!$lang || $lang === ($context['lang'] ?? null)) {
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
