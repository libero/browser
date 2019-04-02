<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Event;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\EmptyView;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;

final class BuildViewEventTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_event() : void
    {
        $event = new BuildViewEvent(new Element('name'), new TemplateView(null));

        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @test
     */
    public function it_has_an_object() : void
    {
        $object = new Element('name');

        $event = new BuildViewEvent($object, new TemplateView(null));

        $this->assertEquals($object, $event->getObject());
    }

    /**
     * @test
     */
    public function it_has_a_view() : void
    {
        $view = new TemplateView('template', ['arg' => 'ument'], ['con' => 'text']);

        $event = new BuildViewEvent(new Element('name'), $view);

        $this->assertEquals($view, $event->getView());

        $view = new TemplateView('foo', ['bar' => 'baz'], ['qux' => 'quux']);

        $event->setView($view);

        $this->assertEquals($view, $event->getView());

        $view = new EmptyView();

        $event->setView($view);

        $this->assertEquals($view, $event->getView());
    }
}
