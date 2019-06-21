<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\FrontPdfContentHeaderListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontPdfContentHeaderListenerTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $listener = new FrontPdfContentHeaderListener();

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/content-header.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<front xmlns="http://example.com">foo</front>'];
        yield 'different element' => ['<foo xmlns="http://jats.nlm.nih.gov">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_content_header_template() : void
    {
        $listener = new FrontPdfContentHeaderListener();

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink">
    <article-meta>
        <self-uri content-type="pdf" xlink:href="http://example.com"/>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent($element, new TemplateView('template'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_pdf() : void
    {
        $listener = new FrontPdfContentHeaderListener();

        $element = $this->loadElement('<front xmlns="http://jats.nlm.nih.gov"><article-meta/></front>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/content-header.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_download_argument_set() : void
    {
        $listener = new FrontPdfContentHeaderListener();

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink">
    <article-meta>
        <self-uri content-type="pdf" xlink:href="http://example.com"/>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/content-header.html.twig', ['download' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertSame(['download' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider downloadProvider
     */
    public function it_sets_the_download_argument(string $xml, array $expected) : void
    {
        $listener = new FrontPdfContentHeaderListener();

        $element = $this->loadElement($xml);

        $context = ['bar' => 'baz'];

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/content-header.html.twig', [], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEquals(['download' => $expected], $view->getArguments());
        $this->assertSame(['bar' => 'baz'], $view->getContext());
    }

    public function downloadProvider() : iterable
    {
        yield 'absolute' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink">
    <article-meta>
        <self-uri content-type="pdf" xlink:href="http://example.com/file.pdf"/>
    </article-meta>
</front>
XML
            ,
            [
                'link' => [
                    'href' => 'http://example.com/file.pdf',
                ],
            ],
        ];

        yield 'xml:base' => [
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink" xml:base="http://example.com/">
    <article-meta>
        <self-uri content-type="pdf" xlink:href="file.pdf"/>
    </article-meta>
</front>
XML
            ,
            [
                'link' => [
                    'href' => 'http://example.com/file.pdf',
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_the_uri_is_relative() : void
    {
        $listener = new FrontPdfContentHeaderListener();

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink">
    <article-meta>
        <self-uri content-type="pdf" xlink:href="file.pdf"/>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/content-header.html.twig')
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }
}
