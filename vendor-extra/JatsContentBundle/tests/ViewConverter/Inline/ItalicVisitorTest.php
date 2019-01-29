<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter\Inline;

use FluentDOM;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\JatsContentBundle\ViewConverter\Inline\ItalicVisitor;
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
    public function it_does_nothing_if_it_is_not_a_jats_italic_element(string $xml) : void
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
        $view = $visitor->visit($node, new View(null), $newContext);

        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    public function nodeProvider() : iterable
    {
        yield 'non-element' => ['foo'];
        yield 'different namespace' => ['<italic xmlns="http://example.com">foo</italic>'];
        yield 'different element' => ['<bold xmlns="http://jats.nlm.nih.gov">foo</bold>'];
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

        $xml = FluentDOM::load('<italic xmlns="http://jats.nlm.nih.gov">foo</italic>');
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

        $xml = FluentDOM::load('<italic xmlns="http://jats.nlm.nih.gov">foo</italic>');
        /** @var Element $node */
        $node = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit($node, new View(null, ['text' => 'bar']), $newContext);

        $this->assertNull($view->getTemplate());
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

        $xml = FluentDOM::load('<italic xmlns="http://jats.nlm.nih.gov">foo <bar>baz</bar></italic>');
        /** @var Element $node */
        $node = $xml->documentElement;

        $newContext = ['foo' => 'bar'];
        $view = $visitor->visit($node, new View(null), $newContext);

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
