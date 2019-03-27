<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\CreateView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;

final class HeadingListener
{
    use ConvertsChildren;
    use SimplifiedViewConverterListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, View $view) : View
    {
        if ($view->hasContext('level')) {
            $view = $view->withArgument('level', $view->getContext('level'));
        }

        return $view->withArgument('text', $this->convertChildren($object, $view->getContext()));
    }

    protected function expectedTemplate() : ?string
    {
        return '@LiberoPatterns/heading.html.twig';
    }

    protected function expectedElement() : array
    {
        return [
            '{http://jats.nlm.nih.gov}article-title',
            '{http://jats.nlm.nih.gov}title',
        ];
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
