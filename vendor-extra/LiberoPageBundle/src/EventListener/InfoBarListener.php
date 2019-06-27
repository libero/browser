<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Contracts\Translation\TranslatorInterface;
use const Libero\LiberoPatternsBundle\MAIN_GRID_FULL;

final class InfoBarListener
{
    use ContextAwareTranslation;
    public const ATTENTION = ['name' => 'attention', 'imagePath' => 'attention.svg'];
    public const INFO = ['name' => 'info', 'imagePath' => 'info.svg'];
    public const SUCCESS = ['name' => 'success', 'imagePath' => 'success.svg'];
    public const WARNING = ['name' => 'warning', 'imagePath' => ''];

    private $urlGenerator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onCreatePagePart(CreatePagePartEvent $event) : void
    {
        $context = ['area' => MAIN_GRID_FULL] + $event->getContext();
        $package = new PathPackage('/images/info-bar', new EmptyVersionStrategy());

        $event->addContent(
            new TemplateView(
                '@LiberoPatterns/info-bar.html.twig',
                [   'type' => self::INFO['name'],
                    'image' => [
                        'src' => $package->getUrl(self::INFO['imagePath']),
                    ],
                    'content' => [
                      'text' => $this->translate('libero.page.infobar.demo.text', $context),
                    ],
                ],
                $context
            )
        );
    }
}
