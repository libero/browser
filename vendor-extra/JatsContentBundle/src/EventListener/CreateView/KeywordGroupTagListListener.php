<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\CreateView;

use DOMNodeList;
use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Event\CreateViewEvent;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\ConvertsLists;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Symfony\Contracts\Translation\TranslatorInterface;
use function array_map;
use function count;

final class KeywordGroupTagListListener
{
    use ContextAwareTranslation;
    use ConvertsLists;
    use SimplifiedViewConverterListener;

    private $translationKeys;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator, array $translationKeys = [])
    {
        $this->converter = $converter;
        $this->translator = $translator;
        $this->translationKeys = $translationKeys;
    }

    protected function handle(CreateViewEvent $event) : View
    {
        $object = $event->getObject();
        $view = $event->getView();

        $title = $object->ownerDocument->xpath()
            ->firstOf('jats:title', $object);

        /** @var DOMNodeList|Element[] $keywords */
        $keywords = $object('jats:kwd');

        if (0 === count($keywords)) {
            return $view;
        }

        $type = $object->getAttribute('kwd-group-type');

        if ($title instanceof Element) {
            $title = $this->converter->convert($title, '@LiberoPatterns/heading.html.twig', $view->getContext())
                ->getArguments();
        } elseif (!isset($this->translationKeys[$type])) {
            return $view;
        } else {
            $title = ['text' => $this->translate($this->translationKeys[$type], $view->getContext())];
        }

        return $view
            ->withArgument('title', $title)
            ->withArgument(
                'list',
                [
                    'items' => array_map(
                        function (View $link) : array {
                            return ['content' => $link->getArguments()];
                        },
                        $this->convertList($keywords, '@LiberoPatterns/link.html.twig', $view->getContext())
                    ),
                ]
            );
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/tag-list.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://jats.nlm.nih.gov}kwd-group'];
    }

    protected function unexpectedArguments() : array
    {
        return ['list'];
    }
}
