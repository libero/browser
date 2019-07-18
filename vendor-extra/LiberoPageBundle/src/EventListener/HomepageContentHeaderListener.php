<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Contracts\Translation\TranslatorInterface;
use function array_filter;
use const Libero\LiberoPatternsBundle\MAIN_GRID_FULL;

final class HomepageContentHeaderListener
{
    use ContextAwareTranslation;

    private $image;

    public function __construct(array $image, TranslatorInterface $translator)
    {
        $this->image = $image;
        $this->translator = $translator;
    }

    public function onCreatePagePart(CreatePagePartEvent $event) : void
    {
        if (!$event->isFor('homepage')) {
            return;
        }

        $context = ['area' => MAIN_GRID_FULL] + $event->getContext();

        $contentHeader = new TemplateView(
            '@LiberoPatterns/content-header.html.twig',
            ['contentTitle' => ['text' => $this->translate('libero.page.site_name', $context)]],
            $context
        );

        if (isset($this->image['src'])) {
            $contentHeader = $contentHeader->withArgument(
                'image',
                array_filter(
                    [
                        'image' => ['src' => $this->image['src']],
                        'sources' => $this->image['sources'] ?? [],
                    ]
                )
            );
        }

        $event->addContent($contentHeader);
    }
}
