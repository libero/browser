<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\Error;

use Libero\LiberoPageBundle\Event\BuildErrorEvent;
use Libero\ViewsBundle\Views\TemplateView;
use function is_string;

trait ErrorListener
{
    final public function onBuildError(BuildErrorEvent $event) : void
    {
        $exception = $event->getException();
        $view = $event->getView();

        if (!$this->supportsStatusCode($exception->getStatusCode())) {
            return;
        }

        $view = $this->setImage($view);
        $view = $this->setHeading($view);
        $view = $this->setDetails($view);

        $event->setView($view);
    }

    abstract protected function supportsStatusCode(int $statusCode) : bool;

    protected function image(array $context) : ?string
    {
        return null;
    }

    protected function heading(array $context) : ?string
    {
        return null;
    }

    protected function details(array $context) : ?string
    {
        return null;
    }

    private function setImage(TemplateView $view) : TemplateView
    {
        if ($view->hasArgument('image')) {
            return $view;
        }

        $image = $this->image($view->getContext());

        if (!is_string($image)) {
            return $view;
        }

        return $view->withArgument('image', ['src' => $image]);
    }

    private function setHeading(TemplateView $view) : TemplateView
    {
        if ($view->hasArgument('heading')) {
            return $view;
        }

        $heading = $this->heading($view->getContext());

        if (!is_string($heading)) {
            return $view;
        }

        return $view->withArgument('heading', ['text' => $heading]);
    }

    private function setDetails(TemplateView $view) : TemplateView
    {
        if ($view->hasArgument('details')) {
            return $view;
        }

        $details = $this->details($view->getContext());

        if (!is_string($details)) {
            return $view;
        }

        return $view->withArgument('details', ['text' => $details]);
    }
}
