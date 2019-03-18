<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\ViewConverter;

use Libero\LiberoContentBundle\ViewConverter\SubVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class SubVisitorTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_sub_element(string $xml) : void
    {
        $visitor = new SubVisitor($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $view = $visitor->visit($element, new View(null));

        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<sub xmlns="http://example.com">foo</sub>'];
        yield 'different element' => ['<italic xmlns="http://libero.pub">foo</italic>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_sub_template() : void
    {
        $visitor = new SubVisitor($this->createFailingConverter());

        $element = $this->loadElement('<sub xmlns="http://libero.pub">foo</sub>');

        $view = $visitor->visit($element, new View('template'));

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_text_set() : void
    {
        $visitor = new SubVisitor($this->createFailingConverter());

        $element = $this->loadElement('<sub xmlns="http://libero.pub">foo</sub>');

        $view = $visitor->visit($element, new View(null, ['text' => 'bar']));

        $this->assertNull($view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_template_and_text_argument() : void
    {
        $visitor = new SubVisitor($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<libero:sub xmlns:libero="http://libero.pub">
    foo <libero:bold>bar</libero:bold> baz
</libero:sub>
XML
        );

        $context = ['qux' => 'quux'];

        $view = $visitor->visit($element, new View(null, [], $context));

        $this->assertSame('@LiberoPatterns/sub.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View(
                        null,
                        ['node' => '/libero:sub/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/libero:sub/libero:bold', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/libero:sub/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
