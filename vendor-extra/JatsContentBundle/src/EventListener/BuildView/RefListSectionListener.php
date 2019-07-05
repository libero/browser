<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Libero\ViewsBundle\Views\ViewConverter;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Libero\ViewsBundle\array_has_key;

final class RefListSectionListener
{
    use ContextAwareTranslation;
    use ViewBuildingListener;

    private $converter;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator)
    {
        $this->converter = $converter;
        $this->translator = $translator;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        if (!$view->hasContext('level')) {
            $view = $view->withContext(['level' => 1]);
        }

        $title = $object->ownerDocument->xpath()
            ->firstOf('jats:title', $object);

        if ($title instanceof Element) {
            $title = $this->converter->convert($title, '@LiberoPatterns/heading.html.twig', $view->getContext());

            $view = $view->withArgument('heading', $title instanceof TemplateView ? $title->getArguments() : $title);
        } else {
            $view = $view->withArgument(
                'heading',
                [
                    'text' => $this->translate('libero.page.references', $view->getContext()),
                    'level' => $view->getContext('level'),
                ]
            );
        }

        $childContext = $view->getContext();
        $childContext['level']++;

        return $view->withArgument(
            'content',
            $this->converter->convert($object, '@LiberoPatterns/reference-list.html.twig', $childContext)
        );
    }

    protected function template() : string
    {
        return '@LiberoPatterns/section.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://jats.nlm.nih.gov}ref-list' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'content');
    }
}
