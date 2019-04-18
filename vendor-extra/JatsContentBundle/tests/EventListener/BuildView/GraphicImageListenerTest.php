<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\GraphicImageListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class GraphicImageListenerTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_graphic_element(string $xml) : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView(null));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => [
            <<< XML
<graphic xmlns="http://example.com" xmlns:xlink="http://www.w3.org/1999/xlink"
    xlink:href="http://example.com/image.jpg"/>
XML
            ,
        ];
        yield 'different element' => ['<italic xmlns="http://jats.nlm.nih.gov">foo</italic>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_graphic_template() : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement(
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink"
    xlink:href="http://example.com/image.jpg"/>
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
    public function it_does_nothing_if_there_is_already_an_image_argument_set() : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement(
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink"
    xlink:href="http://example.com/image.jpg"/>
XML
        );

        $event = new BuildViewEvent($element, new TemplateView(null, ['image' => 'foo']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertNull($view->getTemplate());
        $this->assertSame(['image' => 'foo'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_the_href_is_not_absolute() : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement(
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="image.jpg"/>
XML
        );

        $event = new BuildViewEvent($element, new TemplateView(null));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/image.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_the_href_is_not_http() : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement(
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink"
    xlink:href="ftp://example.com/image.jpg"/>
XML
        );

        $event = new BuildViewEvent($element, new TemplateView(null));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/image.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider typeProvider
     */
    public function it_does_nothing_if_there_is_the_type_cannot_be_identified(string $xml) : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView(null));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/image.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function typeProvider() : iterable
    {
        yield 'no type' => [
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink"
    xlink:href="http://example.com/image.tif"/>
XML
            ,
        ];

        yield 'not a web-friendly image type' => [
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink" mimetype="image" mime-subtype="tiff"
    xlink:href="http://example.com/image"/>
XML
            ,
        ];

        yield 'no type' => [
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink" mime-subtype="tiff"
    xlink:href="http://example.com/image"/>
XML
            ,
        ];

        yield 'no subtype' => [
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink" mimetype="image"
    xlink:href="http://example.com/image"/>
XML
            ,
        ];

        yield 'not a web-friendly image extension' => [
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink"
    xlink:href="http://example.com/image.tif"/>
XML
            ,
        ];
    }

    /**
     * @test
     * @dataProvider graphicProvider
     */
    public function it_sets_the_template_and_image_argument(string $xml, array $expected) : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement($xml);
        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new TemplateView(null, [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/image.html.twig', $view->getTemplate());
        $this->assertEquals(['image' => $expected], $view->getArguments());
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }

    public function graphicProvider() : iterable
    {
        yield 'simple' => [
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink"
    xlink:href="http://example.com/image.jpg"/>
XML
            ,
            [
                'src' => 'http://example.com/image.jpg',
                'alt' => '',
            ],
        ];

        yield 'no extension' => [
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink" mimetype="image" mime-subtype="jpeg"
    xlink:href="http://example.com/image"/>
XML
            ,
            [
                'src' => 'http://example.com/image',
                'alt' => '',
            ],
        ];

        yield 'complex' => [
            <<< XML
<graphic xmlns="http://jats.nlm.nih.gov" xmlns:xlink="http://www.w3.org/1999/xlink"
    xlink:href="/bar/image.jpg" xml:base="https://example.com/foo/">
    <alt-text>alt text</alt-text>
</graphic>
XML
            ,
            [
                'src' => 'https://example.com/bar/image.jpg',
                'alt' => 'alt text',
            ],
        ];
    }
}
