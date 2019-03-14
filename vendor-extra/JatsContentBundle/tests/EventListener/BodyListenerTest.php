<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener;

use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\JatsContentBundle\EventListener\BodyListener;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;
use function array_map;

final class BodyListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_it_does_not_find_the_body() : void
    {
        $listener = new BodyListener($this->createFailingConverter());

        $document = $this->loadDocument(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article/>
</libero:item>
XML
        );

        $event = new CreateContentPagePartEvent('template', $document);
        $originalEvent = clone $event;

        $listener->onCreatePageMain($event);

        $this->assertEquals($originalEvent, $event);
    }

    /**
     * @test
     * @dataProvider pageProvider
     */
    public function it_adds_the_body_content(string $xml, array $context, array $expectedBody) : void
    {
        $listener = new BodyListener($this->createDumpingConverter());

        $document = $this->loadDocument($xml);

        $event = new CreateContentPagePartEvent('template', $document, $context);
        $listener->onCreatePageMain($event);

        $this->assertEquals(
            array_map(
                function (array $block) : View {
                    return new View(null, $block);
                },
                $expectedBody
            ),
            $event->getContent()
        );
    }

    public function pageProvider() : iterable
    {
        yield 'en request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:body>
            <jats:p>Paragraph 1</jats:p>
            <jats:sec>
                <jats:title>Section 1</jats:title>
            </jats:sec>
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
                [
                    'node' => '/libero:item/jats:article/jats:body/jats:p[1]',
                    'template' => null,
                    'context' => [
                        'lang' => 'en',
                        'dir' => 'ltr',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
                [
                    'node' => '/libero:item/jats:article/jats:body/jats:sec',
                    'template' => null,
                    'context' => [
                        'lang' => 'en',
                        'dir' => 'ltr',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
                [
                    'node' => '/libero:item/jats:article/jats:body/jats:p[2]',
                    'template' => null,
                    'context' => [
                        'lang' => 'en',
                        'dir' => 'ltr',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
            ],
        ];

        yield 'fr request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:body>
            <jats:p>Paragraph 1</jats:p>
            <jats:sec>
                <jats:title>Section 1</jats:title>
            </jats:sec>
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
                [
                    'node' => '/libero:item/jats:article/jats:body/jats:p[1]',
                    'template' => null,
                    'context' => [
                        'lang' => 'fr',
                        'dir' => 'ltr',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
                [
                    'node' => '/libero:item/jats:article/jats:body/jats:sec',
                    'template' => null,
                    'context' => [
                        'lang' => 'fr',
                        'dir' => 'ltr',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
                [
                    'node' => '/libero:item/jats:article/jats:body/jats:p[2]',
                    'template' => null,
                    'context' => [
                        'lang' => 'fr',
                        'dir' => 'ltr',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
            ],
        ];

        yield 'ar-EG request' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:body>
            <jats:p>Paragraph 1</jats:p>
            <jats:sec>
                <jats:title>Section 1</jats:title>
            </jats:sec>
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
                [
                    'node' => '/libero:item/jats:article/jats:body/jats:p[1]',
                    'template' => null,
                    'context' => [
                        'lang' => 'ar-EG',
                        'dir' => 'rtl',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
                [
                    'node' => '/libero:item/jats:article/jats:body/jats:sec',
                    'template' => null,
                    'context' => [
                        'lang' => 'ar-EG',
                        'dir' => 'rtl',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
                [
                    'node' => '/libero:item/jats:article/jats:body/jats:p[2]',
                    'template' => null,
                    'context' => [
                        'lang' => 'ar-EG',
                        'dir' => 'rtl',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
            ],
        ];
    }
}
