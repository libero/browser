<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPatternsBundle\ViewConverter\Inline;

use FluentDOM;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\LiberoPatternsBundle\ViewConverter\Inline\ItalicVisitor;
use Libero\ViewsBundle\Views\CallbackInlineViewConverter;
use Libero\ViewsBundle\Views\View;
use LogicException;
use PHPUnit\Framework\TestCase;

final class ItalicVisitorTest extends TestCase
{
    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_italic_element(string $xml) : void
    {
        $visitor = new ItalicVisitor(
            new CallbackInlineViewConverter(
                function () : View {
                    throw new LogicException();
                }
            )
        );

        $xml = FluentDOM::load("<foo>${xml}</foo>");
        /** @var NonDocumentTypeChildNode $node */
        $node = $xml->documentElement->firstChild;

        $newContext = [];
        $view = $visitor->visit($node, new View(''), $newContext);

        $this->assertSame('', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    public function nodeProvider() : iterable
    {
        yield 'non-element' => ['foo'];
        yield 'different namespace' => ['<i xmlns="http://example.com">foo</i>'];
        yield 'different element' => ['<b xmlns="http://libero.pub">foo</b>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_template_set() : void
    {
        $visitor = new ItalicVisitor(
            new CallbackInlineViewConverter(
                function () : View {
                    throw new LogicException();
                }
            )
        );

        $xml = FluentDOM::load('<i xmlns="http://libero.pub">foo</i>');
        /** @var Element $node */
        $node = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit($node, new View('template'), $newContext);

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_text_set() : void
    {
        $visitor = new ItalicVisitor(
            new CallbackInlineViewConverter(
                function () : View {
                    throw new LogicException();
                }
            )
        );

        $xml = FluentDOM::load('<i xmlns="http://libero.pub">foo</i>');
        /** @var Element $node */
        $node = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit($node, new View('', ['text' => 'bar']), $newContext);

        $this->assertSame('', $view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_sets_the_template_and_text_argument() : void
    {
        $visitor = new ItalicVisitor(
            new CallbackInlineViewConverter(
                function (NonDocumentTypeChildNode $object, array $context) : View {
                    return new View('child', ['object' => $object, 'context' => $context]);
                }
            )
        );

        $xml = FluentDOM::load('<i xmlns="http://libero.pub">foo <bar>baz</bar></i>');
        /** @var Element $node */
        $node = $xml->documentElement;

        $newContext = ['foo' => 'bar'];
        $view = $visitor->visit($node, new View(''), $newContext);

        $this->assertSame('@LiberoPatterns/italic.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View('child', ['object' => $node->childNodes->item(0), 'context' => ['foo' => 'bar']]),
                    new View('child', ['object' => $node->childNodes->item(1), 'context' => ['foo' => 'bar']]),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['foo' => 'bar'], $newContext);
    }
}
