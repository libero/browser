<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use function Libero\ViewsBundle\array_has_key;
use function Libero\ViewsBundle\string_is;

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

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/heading.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return string_is($element, '{http://jats.nlm.nih.gov}article-title', '{http://jats.nlm.nih.gov}title');
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'text');
    }
}
