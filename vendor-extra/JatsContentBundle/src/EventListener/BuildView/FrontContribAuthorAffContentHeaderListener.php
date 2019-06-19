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

final class FrontContribAuthorAffContentHeaderListener
{
    use ConvertsLists;
    use ViewBuildingListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        /** @var DOMNodeList<Element> $affs */
        $affs = $object('jats:article-meta/jats:contrib-group/jats:contrib[@contrib-type="author"]/jats:aff');

        if (0 === count($affs)) {
            return $view;
        }

        $affiliations = [
            'items' => array_reduce(
                $this->convertList($affs, '@LiberoPatterns/link.html.twig', $view->getContext()),
                static function (array $list, View $view) : array {
                    $list[] = ['content' => $view instanceof TemplateView ? $view->getArguments() : $view];

                    return $list;
                },
                []
            ),
        ];

        return $view->withArgument('affiliations', $affiliations);
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
        return !array_has_key($arguments, 'affiliations');
    }
}
