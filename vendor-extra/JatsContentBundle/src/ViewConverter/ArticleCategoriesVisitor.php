<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class ArticleCategoriesVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        // @fixme - just experimental.
        return $view->withArgument('items',
            [
                'content' => [
                    'attributes' => [
                        'href' => '#',
                    ],
                    'text' => 'Cell Biology',
                ],
            ]);
    }

    protected function expectedTemplate() : string
    {
        return 'tag-list.html.twig';
    }

    protected function expectedElement() : string
    {
        return '{http://jats.nlm.nih.gov}article-categories';
    }

    protected function unexpectedArguments() : array
    {
        return ['items'];
    }
}
