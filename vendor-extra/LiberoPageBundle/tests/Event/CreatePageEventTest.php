<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\Event;

use InvalidArgumentException;
use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class CreatePageEventTest extends TestCase
{
    use PageTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_is_an_event() : void
    {
        $event = new CreatePageEvent($this->createRequest('page'));

        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @test
     */
    public function it_has_a_request() : void
    {
        $request = $this->createRequest('page');

        $event = new CreatePageEvent($request);

        $this->assertEquals($request, $event->getRequest());
    }

    /**
     * @test
     */
    public function it_has_documents() : void
    {
        $document1 = $this->loadDocument('<foo>bar</foo>');
        $document2 = $this->loadDocument('<baz>qux</baz>');
        $documents = ['one' => $document1, 'two' => $document2];

        $event = new CreatePageEvent($this->createRequest('page'), $documents);

        $this->assertEquals($documents, $event->getDocuments());
        $this->assertEquals($document1, $event->getDocument('one'));
        $this->assertEquals($document2, $event->getDocument('two'));

        $this->expectException(InvalidArgumentException::class);
        $event->getDocument('three');
    }

    /**
     * @test
     */
    public function it_has_content() : void
    {
        $event = new CreatePageEvent($this->createRequest('page'));

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
        $event = new CreatePageEvent($this->createRequest('page'), [], ['con' => 'text']);

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
        $event = new CreatePageEvent($this->createRequest('page'));

        $this->assertNull($event->getTitle());

        $event->setTitle('foo');

        $this->assertSame('foo', $event->getTitle());
    }

    /**
     * @test
     */
    public function it_is_for_a_page() : void
    {
        $event = new CreatePageEvent($this->createRequest('page'));

        $this->assertTrue($event->isFor('page'));
        $this->assertFalse($event->isFor('other-page'));
    }
}
