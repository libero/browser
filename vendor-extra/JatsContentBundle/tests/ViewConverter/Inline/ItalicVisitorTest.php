<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter\Inline;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\JatsContentBundle\ViewConverter\Inline\ItalicVisitor;
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
    public function it_does_nothing_if_it_is_not_a_jats_italic_element(string $xml) : void
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
        yield 'different namespace' => ['<italic xmlns="http://example.com">foo</italic>'];
        yield 'different element' => ['<bold xmlns="http://jats.nlm.nih.gov">foo</bold>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_italic_template() : void
    {
        $visitor = new ItalicVisitor($this->createFailingInlineConverter());

        $xml = FluentDOM::load('<italic xmlns="http://jats.nlm.nih.gov">foo</italic>');
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

        $xml = FluentDOM::load('<italic xmlns="http://jats.nlm.nih.gov">foo</italic>');
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
<jats:italic xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:bold>bar</jats:bold> baz
</jats:italic>
XML
        );
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['foo' => 'bar'];
        $view = $visitor->visit($element, new View(null), $newContext);

        $this->assertSame('@LiberoPatterns/italic.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View('child', ['object' => '/jats:italic/text()[1]', 'context' => ['foo' => 'bar']]),
                    new View('child', ['object' => '/jats:italic/jats:bold', 'context' => ['foo' => 'bar']]),
                    new View('child', ['object' => '/jats:italic/text()[2]', 'context' => ['foo' => 'bar']]),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['foo' => 'bar'], $newContext);
    }
}
