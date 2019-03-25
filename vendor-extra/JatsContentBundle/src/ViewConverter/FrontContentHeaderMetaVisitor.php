<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\TranslatingVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FrontContentHeaderMetaVisitor implements ViewConverterVisitor
{
    use SimplifiedVisitor;
    use TranslatingVisitor;

    private $converter;
    private $translator;

    public function __construct(ViewConverter $converter, TranslatorInterface $translator)
    {
        $this->converter = $converter;
        $this->translator = $translator;
    }

    protected function doVisit(Element $object, View $view) : View
    {
        $meta = $this->converter
            ->convert($object, '@LiberoPatterns/content-meta.html.twig', $view->getContext())
            ->getArguments();

        if (empty($meta)) {
            return $view;
        }

        if (!isset($meta['attributes']['aria-label'])) {
            $meta['attributes']['aria-label'] = $this->translate(
                'libero.patterns.content_header.meta.label',
                $view->getContext()
            );
        }

        return $view->withArgument('meta', $meta);
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/content-header.html.twig';
    }

    protected function expectedElement() : array
    {
        return ['{http://jats.nlm.nih.gov}front'];
    }

    protected function unexpectedArguments() : array
    {
        return ['meta'];
    }
}
