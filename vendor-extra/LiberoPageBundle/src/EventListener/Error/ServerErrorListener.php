<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\Error;

use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Symfony\Component\Asset\Packages;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ServerErrorListener
{
    use ContextAwareTranslation;
    use ErrorListener;

    private $packages;

    public function __construct(Packages $packages, TranslatorInterface $translator)
    {
        $this->packages = $packages;
        $this->translator = $translator;
    }

    protected function supportsStatusCode(int $statusCode) : bool
    {
        return $statusCode >= 500 && $statusCode < 600;
    }

    protected function image(array $context) : ?string
    {
        return $this->packages->getUrl('images/error/5xx.svg', 'libero_patterns');
    }

    protected function heading(array $context) : ?string
    {
        return $this->translate('libero.page.error.server_error.heading', $context);
    }

    protected function details(array $context) : ?string
    {
        return $this->translate('libero.page.error.server_error.details', $context);
    }
}
