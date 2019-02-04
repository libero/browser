<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\ViewConverter\Inline;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\LiberoContentBundle\ViewConverter\Inline\ItalicVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;

final class ItalicVisitorTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_italic_element(string $xml) : void
    {
        $visitor = new ItalicVisitor($this->createFailingInlineConverter());

        $xml = FluentDOM::load("<foo>${xml}</foo>");
        /** @var Element $element */
        $element = $xml->documentElement->firstChild;

        $newContext = [];
        $view = $visitor->visit($element, new View(null), $newContext);

        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<i xmlns="http://example.com">foo</i>'];
        yield 'different element' => ['<b xmlns="http://libero.pub">foo</b>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_italic_template() : void
    {
        $visitor = new ItalicVisitor($this->createFailingInlineConverter());

        $xml = FluentDOM::load('<i xmlns="http://libero.pub">foo</i>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit($element, new View('template'), $newContext);

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_text_set() : void
    {
        $visitor = new ItalicVisitor($this->createFailingInlineConverter());

        $xml = FluentDOM::load('<i xmlns="http://libero.pub">foo</i>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit($element, new View(null, ['text' => 'bar']), $newContext);

        $this->assertNull($view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_sets_the_template_and_text_argument() : void
    {
        $visitor = new ItalicVisitor($this->createInlineConverter());

        $xml = FluentDOM::load(
            <<<XML
<libero:i xmlns:libero="http://libero.pub">
    foo <libero:b>bar</libero:b> baz
</libero:i>
XML
        );
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['qux' => 'quux'];
        $view = $visitor->visit($element, new View(null), $newContext);

        $this->assertSame('@LiberoPatterns/italic.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View(null, ['object' => '/libero:i/text()[1]', 'context' => ['qux' => 'quux']]),
                    new View(null, ['object' => '/libero:i/libero:b', 'context' => ['qux' => 'quux']]),
                    new View(null, ['object' => '/libero:i/text()[2]', 'context' => ['qux' => 'quux']]),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $newContext);
    }
}
