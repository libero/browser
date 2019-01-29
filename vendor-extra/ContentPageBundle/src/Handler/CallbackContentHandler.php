<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Handler;

use FluentDOM\DOM\Element;
use function call_user_func;

final class CallbackContentHandler implements ContentHandler
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function handle(Element $documentElement, array $context) : array
    {
        return call_user_func($this->callback, $documentElement, $context);
    }
}
