<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use Symfony\Contracts\Translation\TranslatorInterface;
use function array_map;
use function count;
use function Libero\ViewsBundle\array_has_key;

final class FrontSubjectGroupTeaserListener
{
    use ContextAwareTranslation;
    use ConvertsLists;
    use ViewBuildingListener;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator)
    {
        $this->converter = $converter;
        $this->translator = $translator;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        /** @var DOMNodeList<Element> $subjects */
        $subjects = $object->ownerDocument->xpath()->evaluate(
            'jats:article-meta/jats:article-categories/jats:subj-group[@subj-group-type="heading"]/jats:subject',
            $object
        );

        if (0 === count($subjects)) {
            return $view;
        }

        return $view->withArgument(
            'categories',
            [
                'attributes' => [
                    'aria-label' => $this->translate('libero.patterns.categories.label', $view->getContext()),
                ],
                'items' => array_map(
                    static function (TemplateView $link) : array {
                        return ['content' => $link->getArguments()];
                    },
                    $this->convertList($subjects, '@LiberoPatterns/link.html.twig', $view->getContext())
                ),
            ]
        );
    }

    protected function template() : string
    {
        return '@LiberoPatterns/teaser.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}front' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'categories');
    }
}
