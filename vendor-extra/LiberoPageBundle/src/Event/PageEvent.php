<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\Event;

use Symfony\Component\HttpFoundation\Request;

trait PageEvent
{
    private $context;
    private $request;

    final public function isFor(string $pageType) : bool
    {
        return $pageType === ($this->request->attributes->get('libero_page')['type'] ?? null);
    }

    final public function getContext() : array
    {
        return $this->context;
    }

    final public function setContext(string $key, $value) : void
    {
        $this->context[$key] = $value;
    }

    final public function getRequest() : Request
    {
        return $this->request;
    }
}
