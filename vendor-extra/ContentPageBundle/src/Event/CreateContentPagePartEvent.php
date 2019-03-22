<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Event;

use FluentDOM\DOM\Document;
use Libero\ViewsBundle\Views\View;
use Symfony\Component\EventDispatcher\Event;
use function end;
use function is_array;
use function is_string;
use function key;

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
