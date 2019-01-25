<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Handler;

use FluentDOM\DOM\Element;

interface ContentHandler
{
    public function handle(Element $documentElement, array $context) : array;
}
