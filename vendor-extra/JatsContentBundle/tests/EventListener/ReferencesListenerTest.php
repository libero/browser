<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener;

use Libero\JatsContentBundle\EventListener\ReferencesListener;
use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;
use function array_map;

final class ReferencesListenerTest extends TestCase
{
    use PageTestCase;
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_it_is_not_a_content_page() : void
    {
        $listener = new ReferencesListener($this->createFailingConverter());

        $document = $this->loadDocument(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article>
        <jats:back>
            <jats:ref-list/>
        </jats:back>
    </jats:article>
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
    public function it_does_nothing_if_it_does_not_find_a_ref_list() : void
    {
        $listener = new ReferencesListener($this->createFailingConverter());

        $document = $this->loadDocument(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub" xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article>
        <jats:back/>
    </jats:article>
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
    public function it_adds_references(string $xml, array $context, array $expectedBody) : void
    {
        $listener = new ReferencesListener($this->createDumpingConverter());

        $document = $this->loadDocument($xml);

        $event = new CreatePagePartEvent(
            'template',
            $this->createRequest('content'),
            ['content_item' => $document],
            $context
        );
        $listener->onCreatePagePart($event);

        $this->assertEquals(
            array_map(
                static function (array $block) : TemplateView {
                    return new TemplateView('', $block);
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
    <jats:article>
        <jats:back>
            <jats:ref-list/>
            <jats:ref-list/>
        </jats:back>
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
                    'node' => '/libero:item/jats:article/jats:back/jats:ref-list[1]',
                    'template' => '@LiberoPatterns/section.html.twig',
                    'context' => [
                        'lang' => 'en',
                        'dir' => 'ltr',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
                [
                    'node' => '/libero:item/jats:article/jats:back/jats:ref-list[2]',
                    'template' => '@LiberoPatterns/section.html.twig',
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
    <jats:article>
        <jats:back>
            <jats:ref-list/>
            <jats:ref-list/>
        </jats:back>
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
                    'node' => '/libero:item/jats:article/jats:back/jats:ref-list[1]',
                    'template' => '@LiberoPatterns/section.html.twig',
                    'context' => [
                        'lang' => 'fr',
                        'dir' => 'ltr',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
                [
                    'node' => '/libero:item/jats:article/jats:back/jats:ref-list[2]',
                    'template' => '@LiberoPatterns/section.html.twig',
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
    <jats:article>
        <jats:back>
            <jats:ref-list/>
            <jats:ref-list/>
        </jats:back>
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
                    'node' => '/libero:item/jats:article/jats:back/jats:ref-list[1]',
                    'template' => '@LiberoPatterns/section.html.twig',
                    'context' => [
                        'lang' => 'ar-EG',
                        'dir' => 'rtl',
                        'level' => 2,
                        'area' => 'primary',
                    ],
                ],
                [
                    'node' => '/libero:item/jats:article/jats:back/jats:ref-list[2]',
                    'template' => '@LiberoPatterns/section.html.twig',
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
