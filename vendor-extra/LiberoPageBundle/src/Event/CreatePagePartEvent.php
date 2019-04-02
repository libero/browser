<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\Event;

use FluentDOM\DOM\Document;
use InvalidArgumentException;
use Libero\ViewsBundle\Views\View;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use function end;
use function is_array;
use function is_string;
use function key;

final class CreatePagePartEvent extends Event
{
    use PageEvent;

    private $content = [];
    private $documents;
    private $template;

    public static function name(string $part) : string
    {
        return "libero.page.create.${part}";
    }

    /**
     * @param array<string,Document> $documents
     */
    public function __construct(string $template, Request $request, array $documents = [], array $context = [])
    {
        $this->template = $template;
        $this->request = $request;
        $this->documents = $documents;
        $this->context = $context;
    }

    public function getContent() : array
    {
        return $this->content;
    }

    public function addContent(View ...$views) : void
    {
        foreach ($views as $view) {
            $area = $view->getContext('area');

            if (!is_string($area)) {
                $this->content[] = $view;

                continue;
            }

            $last = end($this->content);
            if (is_array($last) && $area === $last['area']) {
                $key = key($this->content);
                $this->content[$key]['content'][] = $view;

                continue;
            }

            $this->content[] = ['area' => $area, 'content' => [$view]];
        }
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

    public function getTemplate() : string
    {
        return $this->template;
    }
}
