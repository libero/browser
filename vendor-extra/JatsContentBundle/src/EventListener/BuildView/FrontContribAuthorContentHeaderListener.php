<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use function array_reduce;
use function count;
use function Libero\ViewsBundle\array_has_key;

final class FrontContribAuthorContentHeaderListener
{
    use ConvertsLists;
    use ViewBuildingListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        /** @var DOMNodeList<Element> $contribs */
        $contribs = $object('jats:article-meta/jats:contrib-group/jats:contrib[@contrib-type="author"]');

        if (0 === count($contribs)) {
            return $view;
        }

        $context = ['strip_inline' => true] + $view->getContext();

        $authors = [
            'items' => array_reduce(
                $this->convertList($contribs, '@LiberoPatterns/link.html.twig', $context),
                static function (array $list, View $view) : array {
                    $list[] = ['content' => $view instanceof TemplateView ? $view->getArguments() : $view];

                    return $list;
                },
                []
            ),
        ];

        return $view->withArgument('authors', $authors);
    }

    protected function template() : string
    {
        return '@LiberoPatterns/content-header.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}front' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'authors');
    }
}
