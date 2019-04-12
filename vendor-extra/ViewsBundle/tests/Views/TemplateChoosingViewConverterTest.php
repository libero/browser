<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Event\ChooseTemplateEvent;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\StringView;
use Libero\ViewsBundle\Views\TemplateChoosingViewConverter;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class TemplateChoosingViewConverterTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_is_a_view_converter() : void
    {
        $converter = new TemplateChoosingViewConverter(new EventDispatcher(), $this->createFailingConverter());

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

        $converter = new TemplateChoosingViewConverter(
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
                    $this->assertEquals($node, $fallbackNode);
                    $this->assertSame($template, $fallbackTemplate);
                    $this->assertSame($context, $fallbackContext);

                    return $fallback;
                }
            )
        );

        $this->assertSame($fallback, $converter->convert($node, $template, $context));
    }

    public function nonElementProvider() : iterable
    {
        yield 'cdata' => ['<![CDATA[<cdata>]]>'];
        yield 'comment' => ['<!--comment-->'];
        yield 'processing instruction' => ['<?processing instruction?>'];
        yield 'text' => ['text'];
    }

    /**
     * @test
     */
    public function it_falls_back_when_there_is_a_template() : void
    {
        $fallback = new StringView('fallback');
        $template = 'template';

        $converter = new TemplateChoosingViewConverter(
            $this->createMock(EventDispatcherInterface::class),
            new CallbackViewConverter(
                function (
                    NonDocumentTypeChildNode $node,
                    ?string $fallbackTemplate,
                    array $context
                ) use (
                    $fallback,
                    $template
                ) : View {
                    $this->assertSame($template, $fallbackTemplate);

                    return $fallback;
                }
            )
        );

        $this->assertSame($fallback, $converter->convert(new Element('element'), $template));
    }

    /**
     * @test
     */
    public function it_dispatches_an_event() : void
    {
        $converter = new TemplateChoosingViewConverter(
            $dispatcher = new EventDispatcher(),
            $this->createDumpingConverter()
        );

        $node = new Element('element');
        $context = ['con' => 'text'];

        $dispatcher->addListener(
            ChooseTemplateEvent::NAME,
            function (ChooseTemplateEvent $event) use ($context, $node) : void {
                $this->assertEquals($node, $event->getObject());
                $this->assertEquals($context, $event->getContext());
                $this->assertNull($event->getTemplate());

                $event->setTemplate('template');
            }
        );

        $view = $converter->convert($node, null, ['con' => 'text']);

        $this->assertEquals(
            new TemplateView('', ['node' => '/element', 'template' => 'template', 'context' => $context]),
            $view
        );
    }
}
