<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use const Libero\LiberoPatternsBundle\MAIN_GRID_FULL;
use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        ['logo' => [
          'href' => $this->urlGenerator->generate("libero.page.homepage")
          ]
        ],
        $context
      )
    );
  }
}
