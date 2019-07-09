<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\Error;

use Libero\ViewsBundle\Views\ContextAwareTranslation;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use function in_array;

final class NotFoundErrorListener
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
        return in_array($statusCode, [Response::HTTP_NOT_FOUND, Response::HTTP_GONE], true);
    }

    protected function image(array $context) : ?string
    {
        return $this->packages->getUrl('images/error/404.svg', 'libero_patterns');
    }

    protected function heading(array $context) : ?string
    {
        return $this->translate('libero.page.error.not_found.heading', $context);
    }

    protected function details(array $context) : ?string
    {
        return $this->translate('libero.page.error.not_found.details', $context);
    }
}
