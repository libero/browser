<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\LiberoContentBundle\ViewConverter\TitleHeadingVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class TitleHeadingVisitorTest extends TestCase
{
    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_title_element(string $xml) : void
    {
        $visitor = new TitleHeadingVisitor();

        $xml = FluentDOM::load("<foo>${xml}</foo>");
        /** @var Element $element */
        $element = $xml->documentElement->firstChild;

        $newContext = [];
        $view = $visitor->visit($element, new View('@LiberoPatterns/heading.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<title xmlns="http://example.com">foo</title>'];
        yield 'different element' => ['<foo xmlns="http://libero.pub">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_heading_template() : void
    {
        $visitor = new TitleHeadingVisitor();

        $xml = FluentDOM::load('<title xmlns="http://libero.pub">foo</title>');
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
        $visitor = new TitleHeadingVisitor();

        $xml = FluentDOM::load('<title xmlns="http://libero.pub">foo</title>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/heading.html.twig', ['text' => 'bar']),
            $newContext
        );

        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_sets_the_text_argument() : void
    {
        $visitor = new TitleHeadingVisitor();

        $xml = FluentDOM::load('<title xmlns="http://libero.pub">foo <bar>baz</bar></title>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['foo' => 'bar'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/heading.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertEquals(['text' => 'foo baz',], $view->getArguments());
        $this->assertSame(['foo' => 'bar'], $newContext);
    }
}
