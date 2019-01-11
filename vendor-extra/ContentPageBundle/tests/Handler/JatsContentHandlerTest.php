<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\Handler;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Handler\ContentHandler;
use Libero\ContentPageBundle\Handler\JatsContentHandler;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class JatsContentHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_content_handler() : void
    {
        $handler = new JatsContentHandler();

        $this->assertInstanceOf(ContentHandler::class, $handler);
    }

    /**
     * @test
     * @dataProvider pageProvider
     */
    public function it_returns_the_title(string $xml, array $context, array $expected) : void
    {
        $handler = new JatsContentHandler();

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
<article xml:lang="en" xmlns="http://jats.nlm.nih.gov">
    <front>
        <article-meta>
            <title-group>
                <article-title>Title</article-title>
            </title-group>
        </article-meta>
    </front>
</article>
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
<article xml:lang="en" xmlns="http://jats.nlm.nih.gov">
    <front>
        <article-meta>
            <title-group>
                <article-title>Title</article-title>
            </title-group>
        </article-meta>
    </front>
</article>
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
<article xml:lang="en" xmlns="http://jats.nlm.nih.gov">
    <front>
        <article-meta>
            <title-group>
                <article-title>Title</article-title>
            </title-group>
        </article-meta>
    </front>
</article>
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

        yield 'complex locales' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<article xml:lang="ar" xmlns="http://jats.nlm.nih.gov">
    <front>
        <article-meta>
            <title-group>
                <article-title xml:lang="de">Title</article-title>
            </title-group>
        </article-meta>
    </front>
</article>
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
                            'attributes' => [
                                'lang' => 'ar',
                                'dir' => 'rtl',
                            ],
                            'contentTitle' => [
                                'attributes' => [
                                    'lang' => 'de',
                                    'dir' => 'ltr',
                                ],
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
     * @dataProvider elementProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_item_element(string $xml) : void
    {
        $handler = new JatsContentHandler();

        $document = FluentDOM::load($xml);
        /** @var Element $documentElement */
        $documentElement = $document->documentElement;

        $this->assertSame([], $handler->handle($documentElement, ['foo' => 'bar']));
    }

    public function elementProvider() : iterable
    {
        yield 'no namespace' => ['<article>foo</article>'];
        yield 'different namespace' => ['<article xmlns="http://example.com">foo</article>'];
        yield 'different element' => ['<foo xmlns="http://jats.nlm.nih.gov">bar</foo>'];
    }

    /**
     * @test
     */
    public function it_fails_if_it_does_not_find_the_front() : void
    {
        $handler = new JatsContentHandler();

        $document = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<article xml:lang="en" xmlns="http://jats.nlm.nih.gov"/>
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
        $handler = new JatsContentHandler();

        $document = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://jats.nlm.nih.gov">
    <front>
        <article-meta>
            <title-group/>
        </article-meta>
    </front>
</article>
XML
        );
        /** @var Element $documentElement */
        $documentElement = $document->documentElement;

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not find a title');

        $handler->handle($documentElement, []);
    }
}
