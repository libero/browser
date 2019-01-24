<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use JsonSerializable;
use function array_replace_recursive;

final class View implements JsonSerializable
{
    private $arguments;
    private $template;

    public function __construct(?string $template, array $arguments = [])
    {
        $this->template = $template;
        $this->arguments = $arguments;
    }

    public function hasArgument(string $key) : bool
    {
        return isset($this->arguments[$key]);
    }

    public function getArgument(string $key)
    {
        return $this->arguments[$key] ?? null;
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }

    public function getTemplate() : ?string
    {
        return $this->template;
    }

    public function withArgument(string $key, $value) : View
    {
        return $this->withArguments([$key => $value]);
    }

    public function withArguments(array $arguments) : View
    {
        if ($arguments === $this->arguments || !$arguments) {
            return $this;
        }

        $view = clone $this;

        $view->arguments = array_replace_recursive($view->arguments, $arguments);

        return $view;
    }

    public function withTemplate(string $template) : View
    {
        if ($template === $this->template) {
            return $this;
        }

        $view = clone $this;

        $view->template = $template;

        return $view;
    }

    public function jsonSerialize() : array
    {
        return [
            'template' => $this->template,
            'arguments' => $this->arguments,
        ];
    }
}
