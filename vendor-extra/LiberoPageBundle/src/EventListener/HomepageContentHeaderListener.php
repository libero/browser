<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\ViewsBundle\Views\TranslatingVisitor;
use Libero\ViewsBundle\Views\View;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HomepageContentHeaderListener
{
    use TranslatingVisitor;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onCreatePage(CreatePageEvent $event) : void
    {
        if ('homepage' !== $event->getRequest()->attributes->get('libero_page')['type']) {
            return;
        }

        $event->addContent(
            new View(
                '@LiberoPatterns/content-header.html.twig',
                ['contentTitle' => ['text' => $this->translate('libero.page.site_name', $event->getContext())]],
                $event->getContext()
            )
        );
    }
}
