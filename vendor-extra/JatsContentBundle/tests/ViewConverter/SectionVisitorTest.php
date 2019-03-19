<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use Libero\JatsContentBundle\ViewConverter\SectionVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class SectionVisitorTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_sec_element(string $xml) : void
    {
        $visitor = new SectionVisitor($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $view = $visitor->visit($element, new View(null));

        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<sec xmlns="http://example.com">foo</sec>'];
        yield 'different element' => ['<p xmlns="http://jats.nlm.nih.gov">foo</p>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_section_template() : void
    {
        $visitor = new SectionVisitor($this->createFailingConverter());

        $element = $this->loadElement('<sec xmlns="http://jats.nlm.nih.gov">foo</sec>');

        $view = $visitor->visit($element, new View('template'));

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_content_set() : void
    {
        $visitor = new SectionVisitor($this->createFailingConverter());

        $element = $this->loadElement('<sec xmlns="http://jats.nlm.nih.gov">foo</sec>');

        $view = $visitor->visit($element, new View(null, ['content' => 'bar']));

        $this->assertNull($view->getTemplate());
        $this->assertSame(['content' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider contentProvider
     */
    public function it_sets_the_heading_and_content_arguments(string $xml, array $expectedArguments) : void
    {
        $visitor = new SectionVisitor($this->createDumpingConverter());

        $element = $this->loadElement($xml);
        $context = ['level' => 1];

        $view = $visitor->visit($element, new View(null, [], $context));

        $this->assertSame('@LiberoPatterns/section.html.twig', $view->getTemplate());
        $this->assertEquals($expectedArguments, $view->getArguments());
        $this->assertSame($context, $view->getContext());
    }

    public function contentProvider() : iterable
    {
        yield 'no heading' => [
            <<<XML
<jats:sec xmlns:jats="http://jats.nlm.nih.gov">
    <jats:p>foo</jats:p>
    <jats:p>bar</jats:p>
</jats:sec>
XML
            ,
            [
                'content' => [
                    new View(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[1]',
                            'template' => null,
                            'context' => ['level' => 2],
                        ]
                    ),
                    new View(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[2]',
                            'template' => null,
                            'context' => ['level' => 2],
                        ]
                    ),
                ],
            ],
        ];

        yield 'heading' => [
            <<<XML
<jats:sec xmlns:jats="http://jats.nlm.nih.gov">
    <jats:title>foo</jats:title>
    <jats:p>bar</jats:p>
    <jats:p>baz</jats:p>
</jats:sec>
XML
            ,
            [
                'heading' => [
                    'node' => '/jats:sec/jats:title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['level' => 1],
                ],
                'content' => [
                    new View(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[1]',
                            'template' => null,
                            'context' => ['level' => 2],
                        ]
                    ),
                    new View(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[2]',
                            'template' => null,
                            'context' => ['level' => 2],
                        ]
                    ),
                ],
            ],
        ];
    }
}
