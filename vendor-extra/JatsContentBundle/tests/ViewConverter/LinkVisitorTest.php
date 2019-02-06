<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\JatsContentBundle\ViewConverter\LinkVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;

final class LinkVisitorTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_link_template() : void
    {
        $visitor = new LinkVisitor($this->createFailingConverter());

        $xml = FluentDOM::load('<ext-link xmlns="http://jats.nlm.nih.gov">foo</ext-link>');
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
        $visitor = new LinkVisitor($this->createFailingConverter());

        $xml = FluentDOM::load('<ext-link xmlns="http://jats.nlm.nih.gov">foo</ext-link>');
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
     * @dataProvider textProvider
     */
    public function it_sets_the_template_and_text_argument(string $xml, array $expectedText) : void
    {
        $visitor = new LinkVisitor($this->createDumpingConverter());

        $xml = FluentDOM::load($xml);
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['qux' => 'quux'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/link.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEquals(['text' => $expectedText], $view->getArguments());
        $this->assertSame(['qux' => 'quux'], $newContext);
    }

    public function textProvider() : iterable
    {
        yield 'ext-link' => [
            <<<XML
<jats:ext-link xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:ext-link>
XML
            ,
            [
                new View(
                    null,
                    [
                        'node' => '/jats:ext-link/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:ext-link/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:ext-link/text()[2]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
        ];

        yield 'kwd' => [
            <<<XML
<jats:kwd xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:kwd>
XML
            ,
            [
                new View(
                    null,
                    [
                        'node' => '/jats:kwd/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:kwd/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:kwd/text()[2]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
        ];
    }
}
