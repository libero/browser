<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\Event;

use InvalidArgumentException;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\StringView;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class CreatePagePartEventTest extends TestCase
{
    use PageTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_is_an_event() : void
    {
        $event = new CreatePagePartEvent('template', $this->createRequest('page'));

        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @test
     */
    public function it_has_documents() : void
    {
        $document1 = $this->loadDocument('<foo>bar</foo>');
        $document2 = $this->loadDocument('<baz>qux</baz>');
        $documents = ['one' => $document1, 'two' => $document2];

        $event = new CreatePagePartEvent('template', $this->createRequest('page'), $documents);

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
        $event = new CreatePagePartEvent('template', $this->createRequest('page'));

        $this->assertEmpty($event->getContent());

        $view1 = new TemplateView('template', ['foo']);
        $view2 = new TemplateView('template', [], ['area' => 'one']);
        $view3 = new TemplateView('template', [], ['area' => 'one']);
        $view4 = new TemplateView('template', [], ['area' => 'two']);
        $view5 = new StringView('bar');
        $view6 = new TemplateView('template', [], ['area' => 'one']);
        $view7 = new TemplateView('template', [], ['area' => 'one']);
        $event->addContent($view1, $view2);
        $event->addContent($view3, $view4);
        $event->addContent($view5, $view6);
        $event->addContent($view7);

        $this->assertEquals(
            [
                $view1,
                ['area' => 'one', 'content' => [$view2, $view3]],
                ['area' => 'two', 'content' => [$view4]],
                $view5,
                ['area' => 'one', 'content' => [$view6, $view7]],
            ],
            $event->getContent()
        );
    }

    /**
     * @test
     */
    public function it_has_context() : void
    {
        $event = new CreatePagePartEvent('template', $this->createRequest('page'), [], ['con' => 'text']);

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
    public function it_has_a_template() : void
    {
        $event = new CreatePagePartEvent('template', $this->createRequest('page'));

        $this->assertSame('template', $event->getTemplate());
    }

    /**
     * @test
     */
    public function it_is_for_a_page() : void
    {
        $event = new CreatePagePartEvent('template', $this->createRequest('page'));

        $this->assertTrue($event->isFor('page'));
        $this->assertFalse($event->isFor('other-page'));
    }
}
