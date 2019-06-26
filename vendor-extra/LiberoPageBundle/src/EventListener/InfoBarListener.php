<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Contracts\Translation\TranslatorInterface;
use const Libero\LiberoPatternsBundle\MAIN_GRID_FULL;

final class InfoBarListener
{
    use ContextAwareTranslation;
    public const ATTENTION = ['name' => 'attention', 'imagePath' => 'bundles/liberopatterns/images/attention.svg'];
    public const INFO = ['name' => 'info', 'imagePath' => 'bundles/liberopatterns/images/info.svg'];
    public const SUCCESS = ['name' => 'success', 'imagePath' => 'bundles/liberopatterns/images/success.svg'];
    public const WARNING = ['name' => 'warning', 'imagePath' => ''];

    private $urlGenerator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onCreatePagePart(CreatePagePartEvent $event) : void
    {
        $context = ['area' => MAIN_GRID_FULL] + $event->getContext();

        $event->addContent(
            new TemplateView(
                '@LiberoPatterns/info-bar.html.twig',
                [   'type' => self::INFO['name'],
                    'imagePath' => self::INFO['imagePath'],
                    'content' => [
                      'text' => $this->translate('libero.page.infobar.demo.text', $context),
                    ],
                ],
                $context
            )
        );
    }
}
