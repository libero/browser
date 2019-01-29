<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\JatsContentBundle\ViewConverter\TitleGroupHeadingVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class TitleGroupHeadingVisitorTest extends TestCase
{
    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_title_group_element(string $xml) : void
    {
        $visitor = new TitleGroupHeadingVisitor();

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
        yield 'different namespace' => ['<title-group xmlns="http://example.com">foo</title-group>'];
        yield 'different element' => ['<foo xmlns="http://jats.nlm.nih.gov">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_heading_template() : void
    {
        $visitor = new TitleGroupHeadingVisitor();

        $xml = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<title-group xmlns="http://jats.nlm.nih.gov">
    <article-title>foo</article-title>
</title-group>
XML
        );
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
        $visitor = new TitleGroupHeadingVisitor();

        $xml = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<title-group xmlns="http://jats.nlm.nih.gov">
    <article-title>foo</article-title>
</title-group>
XML
        );
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
    public function it_does_nothing_if_does_not_find_the_title() : void
    {
        $visitor = new TitleGroupHeadingVisitor();

        $xml = FluentDOM::load('<title-group xmlns="http://jats.nlm.nih.gov"/>');
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
        $visitor = new TitleGroupHeadingVisitor();

        $xml = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<title-group xmlns="http://jats.nlm.nih.gov">
    <article-title>foo <bar>baz</bar></article-title>
</title-group>
XML
        );
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['foo' => 'bar'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/heading.html.twig'), $newContext);

        /** @var Element $titleGroup */
        $titleGroup = $element->childNodes->item(0);

        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertEquals(['text' => 'foo baz'], $view->getArguments());
        $this->assertSame(['foo' => 'bar'], $newContext);
    }
}
