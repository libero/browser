<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\Handler;

use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Handler\CallbackContentHandler;
use Libero\ContentPageBundle\Handler\ContentHandler;
use PHPUnit\Framework\TestCase;

final class CallbackContentHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_content_handler() : void
    {
        $handler = new CallbackContentHandler(
            function () : array {
                return [];
            }
        );

        $this->assertInstanceOf(ContentHandler::class, $handler);
    }

    /**
     * @test
     */
    public function it_returns_the_results_of_a_callback() : void
    {
        $handler = new CallbackContentHandler(
            function () : array {
                return ['foo' => 'bar'];
            }
        );

        $this->assertSame(['foo' => 'bar'], $handler->handle(new Element('foo'), []));
    }
}
