<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\JatsContentBundle\ViewConverter\FrontContentHeaderVisitor;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\View;
use LogicException;
use PHPUnit\Framework\TestCase;

final class FrontContentHeaderVisitorTest extends TestCase
{
    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $visitor = new FrontContentHeaderVisitor(
            new CallbackViewConverter(
                function () : View {
                    throw new LogicException();
                }
            )
        );

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
        $visitor = new FrontContentHeaderVisitor(
            new CallbackViewConverter(
                function () : View {
                    throw new LogicException();
                }
            )
        );

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
        $visitor = new FrontContentHeaderVisitor(
            new CallbackViewConverter(
                function () : View {
                    throw new LogicException();
                }
            )
        );

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
        $visitor = new FrontContentHeaderVisitor(
            new CallbackViewConverter(
                function () : View {
                    throw new LogicException();
                }
            )
        );

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
        $visitor = new FrontContentHeaderVisitor(
            new CallbackViewConverter(
                function (Element $object, ?string $template, array $context) : View {
                    return new View('child', ['object' => $object, 'template' => $template, 'context' => $context]);
                }
            )
        );

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

        $newContext = ['foo' => 'bar'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig'), $newContext);

        /** @var Element $articleMeta */
        $articleMeta = $element->childNodes->item(0);

        /** @var Element $titleGroup */
        $titleGroup = $articleMeta->childNodes->item(0);

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'contentTitle' => [
                    'object' => $titleGroup->childNodes->item(0),
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['foo' => 'bar'],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['foo' => 'bar'], $newContext);
    }
}
