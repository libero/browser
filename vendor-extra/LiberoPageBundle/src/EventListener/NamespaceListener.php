<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;

final class NamespaceListener
{
    private $prefix;
    private $uri;

    public function __construct(string $prefix, string $uri)
    {
        $this->prefix = $prefix;
        $this->uri = $uri;
    }

    public function onCreatePage(CreatePageEvent $event) : void
    {
        foreach ($event->getDocuments() as $document) {
            $document->registerNamespace($this->prefix, $this->uri);
        }
    }
}
