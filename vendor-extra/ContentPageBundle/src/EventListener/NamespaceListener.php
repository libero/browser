<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\EventListener;

use Libero\ContentPageBundle\Event\CreateContentPageEvent;

final class NamespaceListener
{
    private $prefix;
    private $uri;

    public function __construct(string $prefix, string $uri)
    {
        $this->prefix = $prefix;
        $this->uri = $uri;
    }

    public function onCreatePage(CreateContentPageEvent $event) : void
    {
        $event->getItem()->registerNamespace($this->prefix, $this->uri);
    }
}
