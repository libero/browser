<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Views;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use function array_replace_recursive;
use function call_user_func;
use function is_callable;
use function is_string;

final class View implements JsonSerializable, IteratorAggregate
{
    private $arguments;
    private $callback;
    private $context;
    private $template;

    public function __construct(?string $template, array $arguments = [], array $context = [])
    {
        $this->template = $template;
        $this->arguments = $arguments;
        $this->context = $context;
    }

    public static function lazy(callable $callback) : View
    {
        $view = new View(null);
        $view->callback = $callback;

        return $view;
    }

    public function hasArgument(string $key) : bool
    {
        $this->initialize();

        return isset($this->arguments[$key]);
    }

    public function getArgument(string $key)
    {
        $this->initialize();

        return $this->arguments[$key] ?? null;
    }

    public function getArguments() : array
    {
        $this->initialize();

        return $this->arguments;
    }

    public function getTemplate() : ?string
    {
        $this->initialize();

        return $this->template;
    }

    public function hasContext(string $key) : bool
    {
        $this->initialize();

        return isset($this->context[$key]);
    }

    public function getContext(?string $key = null)
    {
        $this->initialize();

        if (is_string($key)) {
            return $this->context[$key] ?? null;
        }

        return $this->context;
    }

    public function withArgument(string $key, $value) : View
    {
        $this->initialize();

        return $this->withArguments([$key => $value]);
    }

    public function withArguments(array $arguments) : View
    {
        $this->initialize();

        if ($arguments === $this->arguments || !$arguments) {
            return $this;
        }

        $view = clone $this;

        $view->arguments = array_replace_recursive($view->arguments, $arguments);

        return $view;
    }

    public function withTemplate(string $template) : View
    {
        $this->initialize();

        if ($template === $this->template) {
            return $this;
        }

        $view = clone $this;

        $view->template = $template;

        return $view;
    }

    public function withContext(array $context) : View
    {
        $this->initialize();

        $view = clone $this;

        $view->context = array_replace_recursive($view->context, $context);

        return $view;
    }

    public function jsonSerialize() : array
    {
        $this->initialize();

        return [
            'template' => $this->template,
            'arguments' => $this->arguments,
        ];
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->jsonSerialize());
    }

    private function initialize() : void
    {
        if (!is_callable($this->callback)) {
            return;
        }

        $view = call_user_func($this->callback);

        $this->template = $view->template;
        $this->arguments = $view->arguments;
        $this->context = $view->context;

        $this->callback = null;
    }
}
