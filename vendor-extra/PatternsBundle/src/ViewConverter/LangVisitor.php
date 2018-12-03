<?php

declare(strict_types=1);

namespace Libero\PatternsBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\Views\LangAttributes;
use Libero\Views\View;
use Libero\Views\ViewConverterVisitor;

final class LangVisitor implements ViewConverterVisitor
{
    use LangAttributes;

    public function visit(Element $object, View $view, array &$context = []) : View
    {
        if (!empty($view->getArgument('attributes')['lang'])) {
            return $view;
        }

        return $view->withArguments(
            [
                'attributes' => $this->addLangAttribute(
                    $object,
                    $context,
                    $view->getArgument('attributes') ?? []
                ),
            ]
        );
    }
}
