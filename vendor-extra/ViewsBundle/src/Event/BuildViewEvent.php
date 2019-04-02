<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Event;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\TemplateView;
use Symfony\Component\EventDispatcher\Event;

final class BuildViewEvent extends Event
{
    public const NAME = 'libero.view.build';

    private $object;
    private $view;

    public function __construct(Element $object, TemplateView $view)
    {
        $this->object = $object;
        $this->view = $view;
    }

    public function getObject() : Element
    {
        return $this->object;
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
