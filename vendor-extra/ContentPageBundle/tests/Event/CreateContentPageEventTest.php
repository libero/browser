<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\Event;

use Libero\ContentPageBundle\Event\CreateContentPageEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class CreateContentPageEventTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     */
    public function it_is_an_event() : void
    {
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPageEvent($document);

        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @test
     */
    public function it_has_an_item() : void
    {
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPageEvent($document);

        $this->assertSame($document, $event->getItem());
    }

    /**
     * @test
     */
    public function it_has_content() : void
    {
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPageEvent($document);

        $this->assertEmpty($event->getContent());

        $view1 = new View('template', ['foo']);
        $event->setContent('foo', $view1);

        $view2 = new View('template', ['bar']);
        $event->setContent('bar', $view2);

        $view3 = new View('template', ['baz']);
        $event->setContent('foo', $view3);

        $this->assertEquals(
            [
                'foo' => $view3,
                'bar' => $view2,
            ],
            $event->getContent()
        );
    }

    /**
     * @test
     */
    public function it_has_context() : void
    {
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPageEvent($document, ['con' => 'text']);

        $this->assertSame(['con' => 'text'], $event->getContext());

        $event->setContext('foo', 'bar');
        $event->setContext('con', 'baz');

        $this->assertEquals(
            [
                'con' => 'baz',
                'foo' => 'bar',
            ],
            $event->getContext()
        );
    }

    /**
     * @test
     */
    public function it_has_a_title() : void
    {
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPageEvent($document);

        $this->assertNull($event->getTitle());

        $event->setTitle('foo');

        $this->assertSame('foo', $event->getTitle());
    }
}
