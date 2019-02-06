<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\JatsContentBundle\ViewConverter\FrontSubjectGroupContentHeaderVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;

final class FrontSubjectGroupContentHeaderVisitorTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter());

        $xml = FluentDOM::load("<foo>${xml}</foo>");
        /** @var Element $element */
        $element = $xml->documentElement->firstChild;

        $newContext = [];
        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<front xmlns="http://example.com">foo</front>'];
        yield 'different element' => ['<foo xmlns="http://jats.nlm.nih.gov">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_content_header_template() : void
    {
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter());

        $xml = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="heading">
                <subject>foo</subject>
            </subj-group>
        </article-categories>
    </article-meta>
</front>
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
    public function it_does_nothing_if_there_are_no_subject_groups() : void
    {
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter());

        $xml = FluentDOM::load('<front xmlns="http://jats.nlm.nih.gov"><article-meta/></front>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/content-header.html.twig'),
            $newContext
        );

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }
}
