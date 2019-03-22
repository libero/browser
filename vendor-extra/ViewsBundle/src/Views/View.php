<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use function array_replace_recursive;
use function is_string;

final class View implements JsonSerializable, IteratorAggregate
{
    private $arguments;
    private $context;
    private $template;

    public function __construct(?string $template, array $arguments = [], array $context = [])
    {
        $this->template = $template;
        $this->arguments = $arguments;
        $this->context = $context;
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

    public function hasContext(string $key) : bool
    {
        return isset($this->context[$key]);
    }

    public function getContext(?string $key = null)
    {
        if (is_string($key)) {
            return $this->context[$key] ?? null;
        }

        return $this->context;
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

    public function withContext(array $context) : View
    {
        $view = clone $this;

        $view->context = array_replace_recursive($view->context, $context);

        return $view;
    }

    public function jsonSerialize() : array
    {
        return [
            'template' => $this->template,
            'arguments' => $this->arguments,
        ];
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->jsonSerialize());
    }
}
