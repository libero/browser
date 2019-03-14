<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener;

use Libero\ContentPageBundle\Event\CreateContentPagePartEvent;
use Libero\JatsContentBundle\EventListener\ItemTagsListener;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class ItemTagsListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_it_does_not_find_the_front() : void
    {
        $listener = new ItemTagsListener($this->createFailingConverter());

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
    public function it_adds_item_tags(
        string $xml,
        array $context,
        array $expectedItemTags
    ) : void {
        $listener = new ItemTagsListener($this->createDumpingConverter());

        $document = $this->loadDocument($xml);

        $event = new CreateContentPagePartEvent('template', $document, $context);
        $listener->onCreatePageMain($event);

        $this->assertEquals([new View(null, $expectedItemTags)], $event->getContent());
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
                <jats:kwd-group kwd-group-type="foo">
                    <jats:kwd>foo</jats:kwd>
                </jats:kwd-group>
                <jats:kwd-group>
                    <jats:kwd>bar</jats:kwd>
                </jats:kwd-group>
                <jats:kwd-group kwd-group-type="baz">
                    <jats:kwd>baz</jats:kwd>
                </jats:kwd-group>
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
                'node' => '/libero:item/jats:article/jats:front',
                'template' => '@LiberoPatterns/item-tags.html.twig',
                'context' => [
                    'lang' => 'en',
                    'dir' => 'ltr',
                    'area' => 'primary',
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
                <jats:kwd-group kwd-group-type="foo">
                    <jats:kwd>foo</jats:kwd>
                </jats:kwd-group>
                <jats:kwd-group>
                    <jats:kwd>bar</jats:kwd>
                </jats:kwd-group>
                <jats:kwd-group kwd-group-type="baz">
                    <jats:kwd>baz</jats:kwd>
                </jats:kwd-group>
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
                'node' => '/libero:item/jats:article/jats:front',
                'template' => '@LiberoPatterns/item-tags.html.twig',
                'context' => [
                    'lang' => 'fr',
                    'dir' => 'ltr',
                    'area' => 'primary',
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
                <jats:kwd-group kwd-group-type="foo">
                    <jats:kwd>foo</jats:kwd>
                </jats:kwd-group>
                <jats:kwd-group>
                    <jats:kwd>bar</jats:kwd>
                </jats:kwd-group>
                <jats:kwd-group kwd-group-type="baz">
                    <jats:kwd>baz</jats:kwd>
                </jats:kwd-group>
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
                'node' => '/libero:item/jats:article/jats:front',
                'template' => '@LiberoPatterns/item-tags.html.twig',
                'context' => [
                    'lang' => 'ar-EG',
                    'dir' => 'rtl',
                    'area' => 'primary',
                ],
            ],
        ];
    }
}
