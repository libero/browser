<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\ViewConverter;

use Libero\LiberoContentBundle\ViewConverter\FrontContentHeaderVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontContentHeaderVisitorTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_front_element(string $xml) : void
    {
        $visitor = new FrontContentHeaderVisitor($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig'));

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<front xmlns="http://example.com">foo</front>'];
        yield 'different element' => ['<foo xmlns="http://libero.pub">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_content_header_template() : void
    {
        $visitor = new FrontContentHeaderVisitor($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://libero.pub"><title>foo</title></front>');

        $view = $visitor->visit($element, new View('template'));

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_title() : void
    {
        $visitor = new FrontContentHeaderVisitor($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://libero.pub">foo</front>');

        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig'));

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_content_title_set() : void
    {
        $visitor = new FrontContentHeaderVisitor($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://libero.pub"><title>foo</title></front>');

        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/content-header.html.twig', ['contentTitle' => 'bar'])
        );

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertSame(['contentTitle' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_text_argument() : void
    {
        $visitor = new FrontContentHeaderVisitor($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<libero:front xmlns:libero="http://libero.pub">
    <libero:title>foo</libero:title>
</libero:front>
XML
        );

        $context = ['bar' => 'baz'];

        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig', [], $context));

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'contentTitle' => [
                    'node' => '/libero:front/libero:title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['bar' => 'baz'],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['bar' => 'baz'], $view->getContext());
    }
}
