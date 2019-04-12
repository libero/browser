<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\StringView;
use Libero\ViewsBundle\Views\StringViewConverter;
use Libero\ViewsBundle\Views\TemplateViewBuildingViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class StringViewConverterTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_is_a_view_converter() : void
    {
        $converter = new StringViewConverter($this->createFailingConverter());

        $this->assertInstanceOf(ViewConverter::class, $converter);
    }

    /**
     * @test
     * @dataProvider visibleNodeProvider
     */
    public function it_returns_a_string_view_for_visible_nodes(string $node, string $expected) : void
    {
        $converter = new StringViewConverter($this->createFailingConverter());

        $this->assertEquals(
            new StringView($expected, ['bar' => 'baz']),
            $converter->convert($this->loadNode($node), 'template', ['bar' => 'baz'])
        );
    }

    public function visibleNodeProvider() : iterable
    {
        yield 'cdata' => ['<![CDATA[<cdata>]]>', '<cdata>'];
        yield 'element' => ['<foo>element</foo>', 'element'];
        yield 'text' => ['text', 'text'];
    }

    /**
     * @test0
     * @dataProvider hiddenNodeProvider
     */
    public function it_falls_back_on_hidden_nodes(string $node) : void
    {
        $fallback = new StringView('fallback');

        $converter = new TemplateViewBuildingViewConverter(
            $this->createMock(EventDispatcherInterface::class),
            new CallbackViewConverter(
                function () use ($fallback) : View {
                    return $fallback;
                }
            )
        );

        $this->assertSame($fallback, $converter->convert($this->loadNode($node)));
    }

    public function hiddenNodeProvider() : iterable
    {
        yield 'comment' => ['<!--comment-->'];
        yield 'processing instruction' => ['<?processing instruction?>'];
    }
}
