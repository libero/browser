<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Component\Asset\Packages;
use Symfony\Contracts\Translation\TranslatorInterface;
use function is_string;
use const Libero\LiberoPatternsBundle\MAIN_GRID_FULL;

final class InfoBarListener
{
    use ContextAwareTranslation;

    private $key;
    private $packages;

    public function __construct(?string $key, TranslatorInterface $translator, Packages $packages)
    {
        $this->key = $key;
        $this->translator = $translator;
        $this->packages = $packages;
    }

    public function onCreatePagePart(CreatePagePartEvent $event) : void
    {
        if (!is_string($this->key)) {
            return;
        }

        $context = ['area' => MAIN_GRID_FULL] + $event->getContext();

        $event->addContent(
            new TemplateView(
                '@LiberoPatterns/info-bar.html.twig',
                [
                    'type' => 'info',
                    'image' => [
                        'src' => $this->packages->getUrl('images/info-bar/info.svg', 'libero_patterns'),
                    ],
                    'content' => new TemplateView(
                        '@LiberoPatterns/raw-html.html.twig',
                        ['html' => $this->translate($this->key, $context)]
                    ),
                ],
                $context
            )
        );
    }
}
