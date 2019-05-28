<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Event;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Event\ChooseTemplateEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;

final class ChooseTemplateEventTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_event() : void
    {
        $event = new ChooseTemplateEvent(new Element('name'));

        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @test
     */
    public function it_has_an_object() : void
    {
        $object = new Element('name');

        $event = new ChooseTemplateEvent($object);

        $this->assertEquals($object, $event->getObject());
    }

    /**
     * @test
     */
    public function it_has_a_template() : void
    {
        $event = new ChooseTemplateEvent(new Element('name'));

        $this->assertNull($event->getTemplate());

        $event->setTemplate('template');

        $this->assertEquals('template', $event->getTemplate());
        $this->assertTrue($event->isPropagationStopped());
    }

    /**
     * @test
     */
    public function it_has_context() : void
    {
        $event = new ChooseTemplateEvent(new Element('name'), ['con' => 'text']);

        $this->assertSame(['con' => 'text'], $event->getContext());
        $this->assertTrue($event->hasContext('con'));
        $this->assertSame('text', $event->getContext('con'));
        $this->assertFalse($event->hasContext('foo'));
    }
}
