<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\EventListener;

use Libero\LiberoContentBundle\EventListener\ContentHeaderListener;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ContentHeaderListenerTest extends TestCase
{
    use PageTestCase;
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_it_is_not_a_content_page() : void
    {
        $listener = new ContentHeaderListener($this->createFailingConverter());

        $document = $this->loadDocument(
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
        );

        $event = new CreatePagePartEvent('template', $this->createRequest('foo'), ['content_item' => $document]);
        $originalEvent = clone $event;

        $listener->onCreatePagePart($event);

        $this->assertEquals($originalEvent, $event);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_it_does_not_find_the_front() : void
    {
        $listener = new ContentHeaderListener($this->createFailingConverter());

        $document = $this->loadDocument(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
</libero:item>
XML
        );

        $event = new CreatePagePartEvent('template', $this->createRequest('content'), ['content_item' => $document]);
        $originalEvent = clone $event;

        $listener->onCreatePagePart($event);

        $this->assertEquals($originalEvent, $event);
    }

    /**
     * @test
     * @dataProvider pageProvider
     */
    public function it_adds_a_content_header(
        string $xml,
        array $context,
        array $expectedContentHeader
    ) : void {
        $listener = new ContentHeaderListener($this->createDumpingConverter());

        $event = new CreatePagePartEvent(
            'template',
            $this->createRequest('content'),
            ['content_item' => $this->loadDocument($xml)],
            $context
        );
        $listener->onCreatePagePart($event);

        $this->assertEquals([new View(null, $expectedContentHeader)], $event->getContent());
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
                'node' => '/libero:item/libero:front',
                'template' => '@LiberoPatterns/content-header.html.twig',
                'context' => [
                    'lang' => 'en',
                    'dir' => 'ltr',
                    'area' => null,
                ],
            ],
        ];

        yield 'en request no title' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <libero:front xml:lang="en"/>
</libero:item>
XML
            ,
            [
                'lang' => 'en',
                'dir' => 'ltr',
            ],
            [
                'node' => '/libero:item/libero:front',
                'template' => '@LiberoPatterns/content-header.html.twig',
                'context' => [
                    'lang' => 'en',
                    'dir' => 'ltr',
                    'area' => null,
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
                'node' => '/libero:item/libero:front',
                'template' => '@LiberoPatterns/content-header.html.twig',
                'context' => [
                    'lang' => 'fr',
                    'dir' => 'ltr',
                    'area' => null,
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
                'node' => '/libero:item/libero:front',
                'template' => '@LiberoPatterns/content-header.html.twig',
                'context' => [
                    'lang' => 'ar-EG',
                    'dir' => 'rtl',
                    'area' => null,
                ],
            ],
        ];
    }
}
