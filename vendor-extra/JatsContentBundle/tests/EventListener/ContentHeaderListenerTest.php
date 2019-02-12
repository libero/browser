<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener;

use FluentDOM;
use Libero\ContentPageBundle\Event\CreateContentPageEvent;
use Libero\JatsContentBundle\EventListener\ContentHeaderListener;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;

final class ContentHeaderListenerTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_it_does_not_find_the_front() : void
    {
        $listener = new ContentHeaderListener($this->createFailingConverter());

        $document = FluentDOM::load(
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
        $document->xpath()->registerNodeNamespaces = true;

        $event = new CreateContentPageEvent($document);
        $originalEvent = clone $event;

        $listener->onCreatePage($event);

        $this->assertEquals($originalEvent, $event);
    }

    /**
     * @test
     * @dataProvider pageProvider
     */
    public function it_sets_the_title_and_adds_a_content_header(
        string $xml,
        array $context,
        ?string $expectedTitle,
        array $expectedContentHeader
    ) : void {
        $listener = new ContentHeaderListener($this->createDumpingConverter());

        $document = FluentDOM::load($xml);
        $document->xpath()->registerNodeNamespaces = true;

        $event = new CreateContentPageEvent($document, $context);
        $listener->onCreatePage($event);

        $this->assertSame($expectedTitle, $event->getTitle());
        $this->assertEquals([new View(null, $expectedContentHeader)], $event->getContent());
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
            [
                'node' => '/libero:item/jats:article/jats:front',
                'template' => '@LiberoPatterns/content-header.html.twig',
                'context' => [
                    'lang' => 'en',
                    'dir' => 'ltr',
                ],
            ],
        ];

        yield 'en request no title' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <libero:meta>
        <libero:id>id</libero:id>
    </libero:meta>
    <jats:article>
        <jats:front/>
    </jats:article>
</libero:item>
XML
            ,
            [
                'lang' => 'en',
                'dir' => 'ltr',
            ],
            null,
            [
                'node' => '/libero:item/jats:article/jats:front',
                'template' => '@LiberoPatterns/content-header.html.twig',
                'context' => [
                    'lang' => 'en',
                    'dir' => 'ltr',
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
            [
                'node' => '/libero:item/jats:article/jats:front',
                'template' => '@LiberoPatterns/content-header.html.twig',
                'context' => [
                    'lang' => 'fr',
                    'dir' => 'ltr',
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
            [
                'node' => '/libero:item/jats:article/jats:front',
                'template' => '@LiberoPatterns/content-header.html.twig',
                'context' => [
                    'lang' => 'ar-EG',
                    'dir' => 'rtl',
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_does_not_replace_an_existing_title() : void
    {
        $listener = new ContentHeaderListener($this->createDumpingConverter());

        $document = FluentDOM::load(
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
        $document->xpath()->registerNodeNamespaces = true;

        $event = new CreateContentPageEvent($document);
        $event->setTitle('Existing Title');
        $listener->onCreatePage($event);

        $this->assertSame('Existing Title', $event->getTitle());
    }
}