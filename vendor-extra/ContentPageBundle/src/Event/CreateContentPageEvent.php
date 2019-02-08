<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Event;

use FluentDOM\DOM\Document;
use Symfony\Component\EventDispatcher\Event;

final class CreateContentPageEvent extends Event
{
    public const NAME = 'libero.page.content';

    private $content = [];
    private $context;
    private $item;
    private $title;

    public function __construct(Document $item, array $context = [])
    {
        $this->item = $item;
        $this->context = $context;
    }

    public function getContent() : array
    {
        return $this->content;
    }

    public function addContent($content) : void
    {
        $this->content[] = $content;
    }

    public function getContext() : array
    {
        return $this->context;
    }

    public function setContext(string $key, $value) : void
    {
        $this->context[$key] = $value;
    }

    public function getItem() : Document
    {
        return $this->item;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }
}
