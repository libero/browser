<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use Libero\JatsContentBundle\ViewConverter\LinkVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class LinkVisitorTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_link_template() : void
    {
        $visitor = new LinkVisitor($this->createFailingConverter());

        $element = $this->loadElement('<subject xmlns="http://jats.nlm.nih.gov">foo</subject>');

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

        $element = $this->loadElement('<subject xmlns="http://jats.nlm.nih.gov">foo</subject>');

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

        $element = $this->loadElement($xml);

        $newContext = ['qux' => 'quux'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/link.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEquals(['text' => $expectedText], $view->getArguments());
        $this->assertSame(['qux' => 'quux'], $newContext);
    }

    public function textProvider() : iterable
    {
        yield 'subject' => [
            <<<XML
<jats:subject xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:subject>
XML
            ,
            [
                new View(
                    null,
                    [
                        'node' => '/jats:subject/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:subject/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:subject/text()[2]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
        ];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_it_is_something_else() : void
    {
        $visitor = new LinkVisitor($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

        $newContext = [];
        $view = $visitor->visit($element, new View('@LiberoPatterns/link.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }
}
