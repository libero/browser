<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Symfony\Contracts\Translation\TranslatorInterface;
use function is_string;

final class TitleListener
{
    use ContextAwareTranslation;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onCreatePage(CreatePageEvent $event) : void
    {
        $siteName = $this->translate('libero.page.site_name', $event->getContext());

        $pageTitle = $event->getTitle();

        if (!is_string($pageTitle)) {
            $event->setTitle($siteName);

            return;
        }

        $event->setTitle(
            $this->translate(
                'libero.page.page_title',
                $event->getContext(),
                ['{page_title}' => $pageTitle, '{site_name}' => $siteName]
            )
        );
    }
}
