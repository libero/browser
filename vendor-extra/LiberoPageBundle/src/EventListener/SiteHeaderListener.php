<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use const Libero\LiberoPatternsBundle\MAIN_GRID_FULL;

final class SiteHeaderListener
{
    use ContextAwareTranslation;

    private $urlGenerator;

    public function __construct(TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator)
    {
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }

    public function onCreatePagePart(CreatePagePartEvent $event) : void
    {
        $context = ['area' => MAIN_GRID_FULL] + $event->getContext();

        $event->addContent(
            new TemplateView(
                '@LiberoPatterns/site-header.html.twig',
                [
                    'logo' => [
                        'href' => $this->urlGenerator->generate('libero.page.homepage'),
                        'image' => [
                            'alt' => $this->translate('libero.page.site_name', $context),
                        ],
                    ],
                    'menu' => [
                        [
                            'attributes' => ['href' => $this->urlGenerator->generate('libero.page.homepage')],
                            'text' => $this->translate('libero.page.menu.home', $context),
                        ],
                    ],
                ],
                $context
            )
        );
    }
}
