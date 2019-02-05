<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\Handler;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\ContentPageBundle\Handler\ContentHandler;
use Libero\JatsContentBundle\Handler\JatsContentHandler;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use UnexpectedValueException;
use function GuzzleHttp\json_encode;

final class JatsContentHandlerTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     */
    public function it_is_a_content_handler() : void
    {
        $handler = new JatsContentHandler($this->createFailingConverter());

        $this->assertInstanceOf(ContentHandler::class, $handler);
    }

    /**
     * @test
     * @dataProvider pageProvider
     */
    public function it_creates_the_page(string $xml, array $context, array $expected) : void
    {
        $handler = new JatsContentHandler($this->createDumpingConverter());

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
        yield 'minimum en request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front>
            <jats:article-meta>
                <jats:title-group>
                    <jats:article-title>Title</jats:article-title>
                </jats:title-group>
            </jats:article-meta>
        </jats:front>
    </jats:article>
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
                        'template' => null,
                        'arguments' => [
                            'node' => '/libero:item/jats:article/jats:front',
                            'template' => '@LiberoPatterns/content-header.html.twig',
                            'context' => [
                                'lang' => 'en',
                                'dir' => 'ltr',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'complete en request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front>
            <jats:article-meta>
                <jats:title-group>
                    <jats:article-title>Title</jats:article-title>
                </jats:title-group>
            </jats:article-meta>
        </jats:front>
        <jats:body>
            <jats:p>Paragraph 1</jats:p>
            <jats:p>Paragraph 2</jats:p>
        </jats:body>
    </jats:article>
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
                        'template' => null,
                        'arguments' => [
                            'node' => '/libero:item/jats:article/jats:front',
                            'template' => '@LiberoPatterns/content-header.html.twig',
                            'context' => [
                                'lang' => 'en',
                                'dir' => 'ltr',
                            ],
                        ],
                    ],
                    [
                        'template' => '@LiberoPatterns/single-column-grid.html.twig',
                        'arguments' => [
                            'content' => [
                                [
                                    'template' => null,
                                    'arguments' => [
                                        'node' => '/libero:item/jats:article/jats:body/jats:p[1]',
                                        'template' => null,
                                        'context' => [
                                            'lang' => 'en',
                                            'dir' => 'ltr',
                                        ],
                                    ],
                                ],
                                [
                                    'template' => null,
                                    'arguments' => [
                                        'node' => '/libero:item/jats:article/jats:body/jats:p[2]',
                                        'template' => null,
                                        'context' => [
                                            'lang' => 'en',
                                            'dir' => 'ltr',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'minimum fr request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front>
            <jats:article-meta>
                <jats:title-group>
                    <jats:article-title>Title</jats:article-title>
                </jats:title-group>
            </jats:article-meta>
        </jats:front>
    </jats:article>
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
                        'template' => null,
                        'arguments' => [
                            'node' => '/libero:item/jats:article/jats:front',
                            'template' => '@LiberoPatterns/content-header.html.twig',
                            'context' => [
                                'lang' => 'fr',
                                'dir' => 'ltr',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'complete fr request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front>
            <jats:article-meta>
                <jats:title-group>
                    <jats:article-title>Title</jats:article-title>
                </jats:title-group>
            </jats:article-meta>
        </jats:front>
        <jats:body>
            <jats:p>Paragraph 1</jats:p>
            <jats:p>Paragraph 2</jats:p>
        </jats:body>
    </jats:article>
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
                        'template' => null,
                        'arguments' => [
                            'node' => '/libero:item/jats:article/jats:front',
                            'template' => '@LiberoPatterns/content-header.html.twig',
                            'context' => [
                                'lang' => 'fr',
                                'dir' => 'ltr',
                            ],
                        ],
                    ],
                    [
                        'template' => '@LiberoPatterns/single-column-grid.html.twig',
                        'arguments' => [
                            'content' => [
                                [
                                    'template' => null,
                                    'arguments' => [
                                        'node' => '/libero:item/jats:article/jats:body/jats:p[1]',
                                        'template' => null,
                                        'context' => [
                                            'lang' => 'fr',
                                            'dir' => 'ltr',
                                        ],
                                    ],
                                ],
                                [
                                    'template' => null,
                                    'arguments' => [
                                        'node' => '/libero:item/jats:article/jats:body/jats:p[2]',
                                        'template' => null,
                                        'context' => [
                                            'lang' => 'fr',
                                            'dir' => 'ltr',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'minimum ar-EG request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front>
            <jats:article-meta>
                <jats:title-group>
                    <jats:article-title>Title</jats:article-title>
                </jats:title-group>
            </jats:article-meta>
        </jats:front>
    </jats:article>
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
                        'template' => null,
                        'arguments' => [
                            'node' => '/libero:item/jats:article/jats:front',
                            'template' => '@LiberoPatterns/content-header.html.twig',
                            'context' => [
                                'lang' => 'ar-EG',
                                'dir' => 'rtl',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'complete ar-EG request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front>
            <jats:article-meta>
                <jats:title-group>
                    <jats:article-title>Title</jats:article-title>
                </jats:title-group>
            </jats:article-meta>
        </jats:front>
        <jats:body>
            <jats:p>Paragraph 1</jats:p>
            <jats:p>Paragraph 2</jats:p>
        </jats:body>
    </jats:article>
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
                        'template' => null,
                        'arguments' => [
                            'node' => '/libero:item/jats:article/jats:front',
                            'template' => '@LiberoPatterns/content-header.html.twig',
                            'context' => [
                                'lang' => 'ar-EG',
                                'dir' => 'rtl',
                            ],
                        ],
                    ],
                    [
                        'template' => '@LiberoPatterns/single-column-grid.html.twig',
                        'arguments' => [
                            'content' => [
                                [
                                    'template' => null,
                                    'arguments' => [
                                        'node' => '/libero:item/jats:article/jats:body/jats:p[1]',
                                        'template' => null,
                                        'context' => [
                                            'lang' => 'ar-EG',
                                            'dir' => 'rtl',
                                        ],
                                    ],
                                ],
                                [
                                    'template' => null,
                                    'arguments' => [
                                        'node' => '/libero:item/jats:article/jats:body/jats:p[2]',
                                        'template' => null,
                                        'context' => [
                                            'lang' => 'ar-EG',
                                            'dir' => 'rtl',
                                        ],
                                    ],
                                ],
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
        $handler = new JatsContentHandler($this->createFailingConverter());

        $document = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <meta>
        <id>id</id>
    </meta>
    <jats:article/>
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
