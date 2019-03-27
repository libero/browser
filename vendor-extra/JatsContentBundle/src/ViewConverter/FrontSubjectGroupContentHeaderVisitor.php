<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\TranslatingVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use Symfony\Contracts\Translation\TranslatorInterface;
use function array_map;
use function count;

final class FrontSubjectGroupContentHeaderVisitor implements ViewConverterVisitor
{
    use ConvertsLists;
    use SimplifiedVisitor;
    use TranslatingVisitor;

    private $converter;
    private $translator;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator)
    {
        $this->converter = $converter;
        $this->translator = $translator;
    }

    protected function handle(Element $object, View $view) : View
    {
        /** @var DOMNodeList|Element[] $subjects */
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
                    'aria-label' => $this->translate(
                        'libero.patterns.content_header.categories.label',
                        $view->getContext()
                    ),
                ],
                'items' => array_map(
                    function (View $link) : array {
                        return ['content' => $link->getArguments()];
                    },
                    $this->convertList($subjects, '@LiberoPatterns/link.html.twig', $view->getContext())
                ),
            ]
        );
    }

    protected function expectedTemplate() : ?string
    {
        return '@LiberoPatterns/content-header.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://jats.nlm.nih.gov}front'];
    }

    protected function unexpectedArguments() : array
    {
        return ['categories'];
    }
}
