<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\JatsContentBundle\ViewConverter\HeadingVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;

final class HeadingVisitorTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_heading_template() : void
    {
        $visitor = new HeadingVisitor($this->createFailingConverter());

        $xml = FluentDOM::load('<article-title xmlns="http://jats.nlm.nih.gov">foo</article-title>');
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
        $visitor = new HeadingVisitor($this->createFailingConverter());

        $xml = FluentDOM::load('<article-title xmlns="http://jats.nlm.nih.gov">foo</article-title>');
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
     * @dataProvider textProvider
     */
    public function it_sets_the_text_argument(string $xml, array $expectedText) : void
    {
        $visitor = new HeadingVisitor($this->createDumpingConverter());

        $xml = FluentDOM::load($xml);
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['qux' => 'quux'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/heading.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertEquals(['text' => $expectedText], $view->getArguments());
        $this->assertSame(['qux' => 'quux'], $newContext);
    }

    public function textProvider() : iterable
    {
        yield 'article-title' => [
            <<<XML
<jats:article-title xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:article-title>
XML
            ,
            [
                new View(
                    null,
                    [
                        'node' => '/jats:article-title/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:article-title/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:article-title/text()[2]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
        ];

        yield 'title' => [
            <<<XML
<jats:title xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:title>
XML
            ,
            [
                new View(
                    null,
                    [
                        'node' => '/jats:title/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:title/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:title/text()[2]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
        ];
    }
}
