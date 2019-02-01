<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class LangVisitor implements ViewConverterVisitor
{
    use LangAttributes;

    public function visit(Element $object, View $view, array &$context = []) : View
    {
        if ($view->hasArgument('attributes') && !empty($view->getArgument('attributes')['lang'])) {
            return $view;
        }

        $attributes = $this->addLangAttribute(
            $object,
            $context,
            $view->getArgument('attributes') ?? []
        );

        if (empty($attributes)) {
            return $view;
        }

        return $view->withArgument('attributes', $attributes);
    }
}
