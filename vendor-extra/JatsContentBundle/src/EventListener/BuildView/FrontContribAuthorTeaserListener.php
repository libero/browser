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
use function array_combine;
use function array_fill_keys;
use function array_keys;
use function array_reduce;
use function array_replace;
use function array_slice;
use function count;
use function iterator_to_array;
use function Libero\ViewsBundle\array_has_key;
use function preg_split;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

final class FrontContribAuthorTeaserListener
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
        /** @var DOMNodeList<Element> $contribs */
        $contribs = $object('jats:article-meta/jats:contrib-group/jats:contrib[@contrib-type="author"][jats:name]');

        if (0 === count($contribs)) {
            return $view;
        }

        $contribs = $this->convertList(
            array_slice(iterator_to_array($contribs), 0, 3),
            '@LiberoPatterns/link.html.twig',
            $view->getContext()
        );

        $contribs = array_combine(
            array_slice(['{author1}', '{author2}', '{author3}'], 0, count($contribs)),
            $contribs
        ) ?: [];

        $text = $this->translate(
            'libero.patterns.teaser.authors',
            $view->getContext(),
            ['{count}' => count($contribs)]
        );

        /** @var array<string> $replacements */
        $replacements = preg_split('/({author(?:1|2|3)})/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $details = [
            'text' => array_replace(
                $replacements,
                ...array_reduce(
                    array_keys($contribs),
                    static function (array $carry, string $key) use ($replacements, $contribs) : array {
                        $carry[] = array_fill_keys(array_keys($replacements, $key, true), $contribs[$key]);

                        return $carry;
                    },
                    []
                )
            ),
        ];

        return $view->withArgument('details', $details);
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
        return !array_has_key($arguments, 'details');
    }
}
