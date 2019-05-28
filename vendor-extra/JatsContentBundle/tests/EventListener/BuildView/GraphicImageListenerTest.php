<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\GraphicImageListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Event\ChooseTemplateEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class GraphicImageListenerTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     * @dataProvider templateChoiceProvider
     */
    public function it_can_choose_a_template(string $xml, ?string $expected) : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement($xml);

        $event = new ChooseTemplateEvent($element);
        $listener->onChooseTemplate($event);

        $this->assertSame($expected, $event->getTemplate());
    }

    public function templateChoiceProvider() : iterable
    {
        yield 'graphic element' => ['<graphic xmlns="http://jats.nlm.nih.gov"/>', '@LiberoPatterns/image.html.twig'];
        yield 'different namespace' => ['<graphic xmlns="http://example.com"/>', null];
        yield 'different element' => ['<italic xmlns="http://jats.nlm.nih.gov"/>', null];
    }

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_graphic_element(string $xml) : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/image.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/image.html.twig', $view->getTemplate());
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
    public function it_does_nothing_if_is_not_the_image_template() : void
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

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/image.html.twig', ['image' => 'foo']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/image.html.twig', $view->getTemplate());
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

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/image.html.twig'));
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

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/image.html.twig'));
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

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/image.html.twig'));
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
    public function it_sets_the_image_argument(string $xml, array $expected) : void
    {
        $listener = new GraphicImageListener();

        $element = $this->loadElement($xml);
        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/image.html.twig', [], $context));
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
