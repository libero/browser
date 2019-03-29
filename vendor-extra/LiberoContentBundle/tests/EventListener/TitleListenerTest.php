<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\EventListener;

use Libero\LiberoContentBundle\EventListener\TitleListener;
use Libero\LiberoPageBundle\Event\CreatePageEvent;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

class TitleListenerTest extends TestCase
{
    use PageTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_it_is_not_a_content_page() : void
    {
        $listener = new TitleListener();

        $document = $this->loadDocument(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <libero:front xml:lang="en">
        <libero:title>New Title</libero:title>
    </libero:front>
</libero:item>
XML
        );

        $event = new CreatePageEvent($this->createRequest('foo'), ['content_item' => $document]);
        $originalEvent = clone $event;

        $listener->onCreatePage($event);

        $this->assertEquals($originalEvent, $event);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_it_does_not_find_the_article_title() : void
    {
        $listener = new TitleListener();

        $document = $this->loadDocument(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <libero:front xml:lang="en"/>
</libero:item>
XML
        );

        $event = new CreatePageEvent($this->createRequest('content'), ['content_item' => $document]);
        $originalEvent = clone $event;

        $listener->onCreatePage($event);

        $this->assertEquals($originalEvent, $event);
    }

    /**
     * @test
     * @dataProvider pageProvider
     */
    public function it_sets_the_title(string $xml, array $context, ?string $expectedTitle) : void
    {
        $listener = new TitleListener();

        $document = $this->loadDocument($xml);
        $event = new CreatePageEvent($this->createRequest('content'), ['content_item' => $document], $context);
        $listener->onCreatePage($event);

        $this->assertSame($expectedTitle, $event->getTitle());
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
            'Title',
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
            'Title',
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
            'Title',
        ];
    }

    /**
     * @test
     */
    public function it_does_not_replace_an_existing_title() : void
    {
        $listener = new TitleListener();

        $document = $this->loadDocument(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <libero:front xml:lang="en">
        <libero:title>New Title</libero:title>
    </libero:front>
</libero:item>
XML
        );

        $event = new CreatePageEvent($this->createRequest('content'), ['content_item' => $document]);
        $event->setTitle('Existing Title');
        $listener->onCreatePage($event);

        $this->assertSame('Existing Title', $event->getTitle());
    }
}
