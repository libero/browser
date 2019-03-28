<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use Symfony\Contracts\Translation\TranslatorInterface;

trait ContextAwareTranslation
{
    /** @var TranslatorInterface */
    private $translator;

    public function translate(string $id, array $context = [], array $parameters = [], ?string $domain = null) : string
    {
        return $this->translator->trans($id, $parameters, $domain, $context['lang'] ?? null);
    }
}
