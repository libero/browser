<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\Handler;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Handler\CallbackContentHandler;
use Libero\ContentPageBundle\Handler\ContentHandler;
use Libero\ContentPageBundle\Handler\DelegatingContentHandler;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class DelegatingContentHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_content_handler() : void
    {
        $handler = new DelegatingContentHandler(
            [
                new CallbackContentHandler(
                    function () : array {
                        return [];
                    }
                ),
            ]
        );

        $this->assertInstanceOf(ContentHandler::class, $handler);
    }

    /**
     * @test
     */
    public function it_returns_the_results_of_a_handler() : void
    {
        $handler = new DelegatingContentHandler(
            [
                new CallbackContentHandler(
                    function () : array {
                        return [];
                    }
                ),
                new CallbackContentHandler(
                    function () : array {
                        return ['one'];
                    }
                ),
                new CallbackContentHandler(
                    function () : array {
                        return ['two'];
                    }
                ),
            ]
        );

        $this->assertInstanceOf(ContentHandler::class, $handler);

        $this->assertSame(['one'], $handler->handle(new Element('foo'), []));
    }

    /**
     * @test
     */
    public function it_fails_if_nothing_handles() : void
    {
        $handler = new DelegatingContentHandler(
            [
                new CallbackContentHandler(
                    function () : array {
                        return [];
                    }
                ),
            ]
        );

        /** @var Element $documentElement */
        $documentElement = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo xmlns="http://example.com">bar</foo>
XML
        )->documentElement;

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unable to handle {http://example.com}foo');

        $handler->handle($documentElement, []);
    }
}
