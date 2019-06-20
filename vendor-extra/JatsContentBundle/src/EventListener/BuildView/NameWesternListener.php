<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use Libero\ViewsBundle\Views\ViewConverter;

final class NameWesternListener
{
    use NameListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function nameOrder() : array
    {
        return ['prefix', 'given-names', 'surname', 'suffix'];
    }

    protected function nameStyles() : array
    {
        return ['', 'islensk', 'western'];
    }
}
