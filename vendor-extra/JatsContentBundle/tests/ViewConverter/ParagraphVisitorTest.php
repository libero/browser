<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use Libero\JatsContentBundle\ViewConverter\ParagraphVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class ParagraphVisitorTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_p_element(string $xml) : void
    {
        $visitor = new ParagraphVisitor($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $newContext = [];
        $view = $visitor->visit($element, new View(null), $newContext);

        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<p xmlns="http://example.com">foo</p>'];
        yield 'different element' => ['<sec xmlns="http://jats.nlm.nih.gov">foo</sec>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_paragraph_template() : void
    {
        $visitor = new ParagraphVisitor($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

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
        $visitor = new ParagraphVisitor($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

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
        $visitor = new ParagraphVisitor($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:p xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:bold>bar</jats:bold> baz
</jats:p>
XML
        );

        $newContext = ['qux' => 'quux'];
        $view = $visitor->visit($element, new View(null), $newContext);

        $this->assertSame('@LiberoPatterns/paragraph.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View(
                        null,
                        ['node' => '/jats:p/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/jats:p/jats:bold', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/jats:p/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $newContext);
    }
}
