<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\StringView;
use Libero\ViewsBundle\Views\StringViewConverter;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use PHPUnit\Framework\TestCase;
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
        yield 'cdata' => ['<![CDATA[c < data]]>', 'c < data'];
        yield 'element' => ['<foo>element</foo>', 'element'];
        yield 'text' => ['text', 'text'];
    }

    /**
     * @test
     * @dataProvider hiddenNodeProvider
     */
    public function it_falls_back_on_hidden_nodes(string $node) : void
    {
        $fallback = new StringView('fallback');

        $node = $this->loadNode($node);
        $template = 'template';
        $context = ['con' => 'text'];

        $converter = new StringViewConverter(
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

    public function hiddenNodeProvider() : iterable
    {
        yield 'comment' => ['<!--comment-->'];
        yield 'processing instruction' => ['<?processing instruction?>'];
    }
}
