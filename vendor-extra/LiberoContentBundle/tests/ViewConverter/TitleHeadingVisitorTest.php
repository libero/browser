<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\ViewConverter;

use Libero\LiberoContentBundle\ViewConverter\TitleHeadingVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class TitleHeadingVisitorTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_title_element(string $xml) : void
    {
        $visitor = new TitleHeadingVisitor($this->createFailingConverter());

        $element = $this->loadElement($xml);

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
        $visitor = new TitleHeadingVisitor($this->createFailingConverter());

        $element = $this->loadElement('<title xmlns="http://libero.pub">foo</title>');

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
        $visitor = new TitleHeadingVisitor($this->createFailingConverter());

        $element = $this->loadElement('<title xmlns="http://libero.pub">foo</title>');

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
        $visitor = new TitleHeadingVisitor($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<libero:title xmlns:libero="http://libero.pub"> 
    foo <libero:italic>bar</libero:italic> baz
</libero:title>
XML
        );

        $newContext = ['qux' => 'quux'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/heading.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View(
                        null,
                        ['node' => '/libero:title/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/libero:title/libero:italic', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/libero:title/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $newContext);
    }
}
