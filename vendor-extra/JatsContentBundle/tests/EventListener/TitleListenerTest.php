<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener;

use Libero\JatsContentBundle\EventListener\TitleListener;
use Libero\LiberoPageBundle\Event\CreatePageEvent;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class TitleListenerTest extends TestCase
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
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front>
            <jats:article-meta>
                <jats:title-group>
                    <jats:article-title>New Title</jats:article-title>
                </jats:title-group>
            </jats:article-meta>
        </jats:front>
    </jats:article>
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
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front>
            <jats:article-meta>
                <jats:title-group/>
            </jats:article-meta>
        </jats:front>
    </jats:article>
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
            'Title',
        ];

        yield 'fr request' => [
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
            'Title',
        ];

        yield 'ar-EG request' => [
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
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front>
            <jats:article-meta>
                <jats:title-group>
                    <jats:article-title>New Title</jats:article-title>
                </jats:title-group>
            </jats:article-meta>
        </jats:front>
    </jats:article>
</libero:item>
XML
        );

        $event = new CreatePageEvent($this->createRequest('content'), ['content_item' => $document]);
        $event->setTitle('Existing Title');
        $listener->onCreatePage($event);

        $this->assertSame('Existing Title', $event->getTitle());
    }
}
