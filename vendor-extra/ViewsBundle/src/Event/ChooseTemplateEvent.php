<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\Event;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\HasContext;
use Symfony\Component\EventDispatcher\Event;

final class ChooseTemplateEvent extends Event
{
    use HasContext;

    public const NAME = 'libero.view.template';

    private $object;
    private $template;

    public function __construct(Element $object, array $context = [])
    {
        $this->object = $object;
        $this->context = $context;
    }

    public function getObject() : Element
    {
        return $this->object;
    }

    public function setTemplate(string $template) : void
    {
        $this->template = $template;
        $this->stopPropagation();
    }

    public function getTemplate() : ?string
    {
        return $this->template;
    }
}
