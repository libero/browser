<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use Libero\ViewsBundle\Views\ViewConverter;

final class NameEasternListener
{
    use NameListener;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function nameOrder() : array
    {
        return ['prefix', 'surname', 'given-names', 'suffix'];
    }

    protected function nameStyles() : array
    {
        return ['eastern'];
    }
}
