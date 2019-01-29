<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\Handler;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Handler\ContentHandler;
use Libero\LiberoContentBundle\Handler\LiberoContentHandler;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class LiberoContentHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_content_handler() : void
    {
        $handler = new LiberoContentHandler();

        $this->assertInstanceOf(ContentHandler::class, $handler);
    }

    /**
     * @test
     * @dataProvider pageProvider
     */
    public function it_returns_the_title(string $xml, array $context, array $expected) : void
    {
        $handler = new LiberoContentHandler();

        $document = FluentDOM::load($xml);
        /** @var Element $documentElement */
        $documentElement = $document->documentElement;

        $this->assertSame($expected, $handler->handle($documentElement, $context));
    }

    public function pageProvider() : iterable
    {
        yield 'en request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <meta>
        <id>id</id>
    </meta>
    <front xml:lang="en">
        <title>Title</title>
    </front>
</item>
XML
            ,
            [
                'lang' => 'en',
                'dir' => 'ltr',
            ],
            [
                'lang' => 'en',
                'dir' => 'ltr',
                'title' => 'Title',
                'content' => [
                    [
                        'template' => '@LiberoPatterns/content-header.html.twig',
                        'arguments' => [
                            'attributes' => [],
                            'contentTitle' => [
                                'attributes' => [],
                                'text' => 'Title',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'fr request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <meta>
        <id>id</id>
    </meta>
    <front xml:lang="en">
        <title>Title</title>
    </front>
</item>
XML
            ,
            [
                'lang' => 'fr',
                'dir' => 'ltr',
            ],
            [
                'lang' => 'fr',
                'dir' => 'ltr',
                'title' => 'Title',
                'content' => [
                    [
                        'template' => '@LiberoPatterns/content-header.html.twig',
                        'arguments' => [
                            'attributes' => [
                                'lang' => 'en',
                            ],
                            'contentTitle' => [
                                'attributes' => [],
                                'text' => 'Title',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'ar-EG request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <meta>
        <id>id</id>
    </meta>
    <front xml:lang="en">
        <title>Title</title>
    </front>
</item>
XML
            ,
            [
                'lang' => 'ar-EG',
                'dir' => 'rtl',
            ],
            [
                'lang' => 'ar-EG',
                'dir' => 'rtl',
                'title' => 'Title',
                'content' => [
                    [
                        'template' => '@LiberoPatterns/content-header.html.twig',
                        'arguments' => [
                            'attributes' => [
                                'lang' => 'en',
                                'dir' => 'ltr',
                            ],
                            'contentTitle' => [
                                'attributes' => [],
                                'text' => 'Title',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_fails_if_it_does_not_find_the_front() : void
    {
        $handler = new LiberoContentHandler();

        $document = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <meta>
        <id>id</id>
    </meta>
</item>
XML
        );
        /** @var Element $documentElement */
        $documentElement = $document->documentElement;

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not find a front');

        $handler->handle($documentElement, []);
    }

    /**
     * @test
     */
    public function it_fails_if_it_does_not_find_the_title() : void
    {
        $handler = new LiberoContentHandler();

        $document = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <meta>
        <id>id</id>
    </meta>
    <front xml:lang="en"/>
</item>
XML
        );
        /** @var Element $documentElement */
        $documentElement = $document->documentElement;

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not find a title');

        $handler->handle($documentElement, []);
    }
}
