<?php

declare(strict_types=1);

namespace Libero\PatternsBundle\ViewConverter\Inline;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\ChildNode;
use Libero\Views\InlineViewConverterVisitor;
use Libero\Views\View;

final class LangVisitor implements InlineViewConverterVisitor
{
    public function visit(ChildNode $object, View $view, array &$context = []) : View
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

        $context['lang'] = $lang;
        $context['dir'] = 'ltr';

        $arguments = [
            'attributes' => [
                'lang' => $context['lang'],
                'dir' => $context['dir'],
            ],
        ];

        return $view->withArguments($arguments);
    }
}
