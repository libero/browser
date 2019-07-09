<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\Event;

use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\EventDispatcher\Event;

final class BuildErrorEvent extends Event
{
    public const NAME = 'libero.page.error.build';

    private $exception;
    private $view;

    public function __construct(FlattenException $exception, TemplateView $view)
    {
        $this->exception = $exception;
        $this->view = $view;
    }

    public function getException() : FlattenException
    {
        return $this->exception;
    }

    public function getView() : TemplateView
    {
        return $this->view;
    }

    public function setView(TemplateView $view) : void
    {
        $this->view = $view;
    }
}
