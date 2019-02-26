<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Event;

use FluentDOM\DOM\Document;
use Symfony\Component\EventDispatcher\Event;
use function array_merge;

final class CreateContentPagePartEvent extends Event
{
    private $content = [];
    private $context;
    private $item;
    private $template;

    public static function name(string $part) : string
    {
        return "libero.page.content.${part}";
    }

    public function __construct(string $template, Document $item, array $context = [])
    {
        $this->template = $template;
        $this->item = $item;
        $this->context = $context;
    }

    public function getContent() : array
    {
        return $this->content;
    }

    public function addContent(string $where, ...$content) : void
    {
        $this->content[$where] = array_merge($this->content[$where] ?? [], $content);
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

    public function getTemplate() : string
    {
        return $this->template;
    }
}
