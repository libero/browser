<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\StringView;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\TemplateViewBuildingViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class TemplateViewBuildingViewConverterTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_is_a_view_converter() : void
    {
        $converter = new TemplateViewBuildingViewConverter(new EventDispatcher(), $this->createFailingConverter());

        $this->assertInstanceOf(ViewConverter::class, $converter);
    }

    /**
     * @test
     * @dataProvider nonElementProvider
     */
    public function it_falls_back_on_non_elements(string $node) : void
    {
        $fallback = new StringView('fallback');

        $node = $this->loadNode($node);
        $template = 'template';
        $context = ['con' => 'text'];

        $converter = new TemplateViewBuildingViewConverter(
            $this->createMock(EventDispatcherInterface::class),
            new CallbackViewConverter(
                function (
                    NonDocumentTypeChildNode $fallbackNode,
                    ?string $fallbackTemplate,
                    array $fallbackContext
                ) use (
                    $context,
                    $fallback,
                    $node,
                    $template
                ) : View {
                    $this->assertEquals($fallbackNode, $node);
                    $this->assertSame($fallbackTemplate, $template);
                    $this->assertSame($fallbackContext, $context);

                    return $fallback;
                }
            )
        );

        $this->assertSame($fallback, $converter->convert($node, $template, $context));
    }

    public function nonElementProvider() : iterable
    {
        yield 'cdata' => ['<![CDATA[c < data>]]>'];
        yield 'comment' => ['<!--comment-->'];
        yield 'processing instruction' => ['<?processing instruction?>'];
        yield 'text' => ['text'];
    }

    /**
     * @test
     */
    public function it_falls_back_when_there_is_no_template() : void
    {
        $fallback = new StringView('fallback');

        $converter = new TemplateViewBuildingViewConverter(
            $this->createMock(EventDispatcherInterface::class),
            new CallbackViewConverter(
                function (NonDocumentTypeChildNode $node, ?string $template, array $context) use ($fallback) : View {
                    $this->assertNull($template);

                    return $fallback;
                }
            )
        );

        $this->assertSame($fallback, $converter->convert(new Element('element')));
    }

    /**
     * @test
     */
    public function it_dispatches_an_event() : void
    {
        $dispatcher = new EventDispatcher();
        $converter = new TemplateViewBuildingViewConverter($dispatcher, $this->createFailingConverter());

        $node = new Element('element');

        $expected = new TemplateView('changed', ['one' => 'two'], ['three' => 'four']);

        $dispatcher->addListener(
            BuildViewEvent::NAME,
            function (BuildViewEvent $event) use ($expected, $node) : void {
                $this->assertEquals($node, $event->getObject());
                $this->assertEquals(new TemplateView('template', [], ['con' => 'text']), $event->getView());

                $event->setView($expected);
            }
        );

        $view = $converter->convert($node, 'template', ['con' => 'text']);

        $this->assertEquals($expected, $view);
    }
}
