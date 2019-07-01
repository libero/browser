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
use function array_keys;
use function array_map;
use function count;
use function Libero\ViewsBundle\array_has_key;
use function preg_split;
use function substr;
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

        $text = $this->translate(
            'libero.patterns.teaser.authors',
            $view->getContext(),
            ['{count}' => count($contribs)]
        );

        /** @var array<string> $replacements */
        $replacements = preg_split(
            '/{author([1-9][0-9]*)}/',
            " {$text}",
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        $replacements[0] = substr($replacements[0], 1);

        $details = [
            'text' => array_map(
                function (int $key) use ($contribs, $replacements, $view) {
                    if (~$key & 1) {
                        return $replacements[$key];
                    }

                    $contrib = $contribs->item((int) $replacements[$key] - 1);

                    if (!$contrib instanceof Element) {
                        return "{author{$replacements[$key]}}";
                    }

                    return $this->converter->convert($contrib, '@LiberoPatterns/link.html.twig', $view->getContext());
                },
                array_keys($replacements)
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
