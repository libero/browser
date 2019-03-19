<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\ViewsBundle\Views\TranslatingVisitor;
use Symfony\Contracts\Translation\TranslatorInterface;
use function is_string;

final class TitleListener
{
    use TranslatingVisitor;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onCreatePage(CreatePageEvent $event) : void
    {
        $title = $event->getTitle();

        if (is_string($title)) {
            $title .= ' | ';
        }

        $event->setTitle($title.$this->translate('libero.page.site_name', $event->getContext()));
    }
}
