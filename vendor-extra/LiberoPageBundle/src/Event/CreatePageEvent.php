<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\Event;

use FluentDOM\DOM\Document;
use InvalidArgumentException;
use Libero\ViewsBundle\Views\View;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

final class CreatePageEvent extends Event
{
    use PageEvent;

    public const NAME = 'libero.page.create';

    private $content = [];
    private $documents;
    private $title;

    /**
     * @param array<string,Document> $documents
     */
    public function __construct(Request $request, array $documents = [], array $context = [])
    {
        $this->request = $request;
        $this->documents = $documents;
        $this->context = $context;
    }

    public function getContent() : array
    {
        return $this->content;
    }

    public function setContent(string $area, View $view) : void
    {
        $this->content[$area] = $view;
    }

    public function getDocument(string $key) : Document
    {
        if (!isset($this->documents[$key])) {
            throw new InvalidArgumentException("Unknown document '{$key}'");
        }

        return $this->documents[$key];
    }

    /**
     * @return array<string,Document>
     */
    public function getDocuments() : array
    {
        return $this->documents;
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
