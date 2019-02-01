<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\Handler;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Handler\ContentHandler;
use Libero\LiberoContentBundle\Handler\LiberoContentHandler;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\View;
use LogicException;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use UnexpectedValueException;
use function GuzzleHttp\json_encode;

final class LiberoContentHandlerTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     */
    public function it_is_a_content_handler() : void
    {
        $handler = new LiberoContentHandler(
            new CallbackViewConverter(
                function () : View {
                    throw new LogicException();
                }
            )
        );

        $this->assertInstanceOf(ContentHandler::class, $handler);
    }

    /**
     * @test
     * @dataProvider pageProvider
     */
    public function it_returns_the_title(string $xml, array $context, array $expected) : void
    {
        $handler = new LiberoContentHandler($this->createConverter());

        $document = FluentDOM::load($xml);
        /** @var Element $documentElement */
        $documentElement = $document->documentElement;

        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            json_encode($handler->handle($documentElement, $context))
        );
    }

    public function pageProvider() : iterable
    {
        yield 'en request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <libero:front xml:lang="en">
        <libero:title>Title</libero:title>
    </libero:front>
</libero:item>
XML
            ,
            [
                'lang' => 'en',
                'dir' => 'ltr',
            ],
            [
                'lang' => 'en',
                'dir' => 'ltr',
                'title' => null,
                'content' => [
                    [
                        'template' => '@LiberoPatterns/content-header.html.twig',
                        'arguments' => [
                            'element' => '/libero:item/libero:front',
                            'context' => [
                                'lang' => 'en',
                                'dir' => 'ltr',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'fr request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <libero:front xml:lang="en">
        <libero:title>Title</libero:title>
    </libero:front>
</libero:item>
XML
            ,
            [
                'lang' => 'fr',
                'dir' => 'ltr',
            ],
            [
                'lang' => 'fr',
                'dir' => 'ltr',
                'title' => null,
                'content' => [
                    [
                        'template' => '@LiberoPatterns/content-header.html.twig',
                        'arguments' => [
                            'element' => '/libero:item/libero:front',
                            'context' => [
                                'lang' => 'fr',
                                'dir' => 'ltr',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'ar-EG request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <libero:front xml:lang="en">
        <libero:title>Title</libero:title>
    </libero:front>
</libero:item>
XML
            ,
            [
                'lang' => 'ar-EG',
                'dir' => 'rtl',
            ],
            [
                'lang' => 'ar-EG',
                'dir' => 'rtl',
                'title' => null,
                'content' => [
                    [
                        'template' => '@LiberoPatterns/content-header.html.twig',
                        'arguments' => [
                            'element' => '/libero:item/libero:front',
                            'context' => [
                                'lang' => 'ar-EG',
                                'dir' => 'rtl',
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
        $handler = new LiberoContentHandler(
            new CallbackViewConverter(
                function () : View {
                    throw new LogicException();
                }
            )
        );

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
}
