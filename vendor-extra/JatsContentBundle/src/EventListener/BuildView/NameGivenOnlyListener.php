<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use Libero\ViewsBundle\Views\ViewConverter;

final class NameGivenOnlyListener
{
    use NameListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function nameOrder() : array
    {
        return ['prefix', 'given-names', 'suffix'];
    }

    protected function nameStyles() : array
    {
        return ['given-only'];
    }
}
