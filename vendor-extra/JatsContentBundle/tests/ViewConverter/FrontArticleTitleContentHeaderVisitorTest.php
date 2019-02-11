<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\JatsContentBundle\ViewConverter\FrontArticleTitleContentHeaderVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;

final class FrontArticleTitleContentHeaderVisitorTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $visitor = new FrontArticleTitleContentHeaderVisitor($this->createFailingConverter());

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
        $visitor = new FrontArticleTitleContentHeaderVisitor($this->createFailingConverter());

        $xml = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <title-group>
            <article-title>foo</article-title>
        </title-group>
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
    public function it_does_nothing_if_there_is_no_title_group() : void
    {
        $visitor = new FrontArticleTitleContentHeaderVisitor($this->createFailingConverter());

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

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_content_title_set() : void
    {
        $visitor = new FrontArticleTitleContentHeaderVisitor($this->createFailingConverter());

        $xml = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <title-group>
            <article-title>foo</article-title>
        </title-group>
    </article-meta>
</front>
XML
        );
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/content-header.html.twig', ['contentTitle' => 'bar']),
            $newContext
        );

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertSame(['contentTitle' => 'bar'], $view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_sets_the_text_argument() : void
    {
        $visitor = new FrontArticleTitleContentHeaderVisitor($this->createDumpingConverter());

        $xml = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-meta>
        <jats:title-group>
            <jats:article-title>foo</jats:article-title>
        </jats:title-group>
    </jats:article-meta>
</jats:front>
XML
        );
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['bar' => 'baz'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'contentTitle' => [
                    'node' => '/jats:front/jats:article-meta/jats:title-group/jats:article-title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['bar' => 'baz'],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['bar' => 'baz'], $newContext);
    }
}
