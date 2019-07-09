<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\Error;

use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ServiceUnavailableErrorListener
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
        return Response::HTTP_SERVICE_UNAVAILABLE === $statusCode;
    }

    protected function image(array $context) : ?string
    {
        return $this->packages->getUrl('images/error/503.svg', 'libero_patterns');
    }

    protected function heading(array $context) : ?string
    {
        return $this->translate('libero.page.error.service_unavailable.heading', $context);
    }

    protected function details(array $context) : ?string
    {
        return $this->translate('libero.page.error.service_unavailable.details', $context);
    }
}
