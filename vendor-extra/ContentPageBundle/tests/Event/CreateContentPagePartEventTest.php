<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\Event;

use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class CreateContentPagePartEventTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     */
    public function it_is_an_event() : void
    {
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPagePartEvent('template', $document);

        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @test
     */
    public function it_has_an_item() : void
    {
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPagePartEvent('template', $document);

        $this->assertSame($document, $event->getItem());
    }

    /**
     * @test
     */
    public function it_has_content() : void
    {
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPagePartEvent('template', $document);

        $this->assertEmpty($event->getContent());

        $view1 = new View('template', ['foo']);
        $view2 = new View('template', [], ['area' => 'one']);
        $view3 = new View('template', [], ['area' => 'one']);
        $view4 = new View('template', [], ['area' => 'two']);
        $view5 = new View('template', ['bar']);
        $view6 = new View('template', [], ['area' => 'one']);
        $view7 = new View('template', [], ['area' => 'one']);
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
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPagePartEvent('template', $document, ['con' => 'text']);

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
        $document = $this->loadDocument('<foo><bar>baz</bar></foo>');

        $event = new CreateContentPagePartEvent('template', $document);

        $this->assertSame('template', $event->getTemplate());
    }
}
