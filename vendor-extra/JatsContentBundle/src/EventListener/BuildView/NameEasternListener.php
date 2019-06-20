<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use Libero\ViewsBundle\Views\ViewConverter;
use function preg_match;

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

    protected function addSpaces(string $lang) : bool
    {
        return 0 === preg_match('/^(?:ja|ko|zh)(?:$|-)/', $lang);
    }
}
