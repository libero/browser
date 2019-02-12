<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use Libero\JatsContentBundle\ViewConverter\FrontSubjectGroupContentHeaderVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class FrontSubjectGroupContentHeaderVisitorTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter());

        $element = $this->loadElement($xml);

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
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="heading">
                <subject>foo</subject>
            </subj-group>
        </article-categories>
    </article-meta>
</front>
XML
        );

        $newContext = [];
        $view = $visitor->visit($element, new View('template'), $newContext);

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_are_no_subject_groups() : void
    {
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="not-heading">
                <subject>foo</subject>
            </subj-group>
        </article-categories>
    </article-meta>
</front>
XML
        );

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
    public function it_does_nothing_if_there_is_already_categories_set() : void
    {
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="heading">
                <subject>foo</subject>
            </subj-group>
        </article-categories>
    </article-meta>
</front>
XML
        );

        $newContext = [];
        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/content-header.html.twig', ['categories' => 'bar']),
            $newContext
        );

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertSame(['categories' => 'bar'], $view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_sets_the_categories_argument() : void
    {
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-meta>
        <jats:article-categories>
            <jats:subj-group subj-group-type="heading">
                <jats:subject>foo</jats:subject>
                <jats:subject>bar</jats:subject>
            </jats:subj-group>
            <jats:subj-group subj-group-type="heading">
                <jats:subject>baz</jats:subject>
            </jats:subj-group>
        </jats:article-categories>
    </jats:article-meta>
</jats:front>
XML
        );

        $newContext = ['qux' => 'quux'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'categories' => [
                    'items' => [
                        [
                            'content' => [
                                'node' => '/jats:front/jats:article-meta/jats:article-categories/'
                                    .'jats:subj-group[1]/jats:subject[1]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['qux' => 'quux'],
                            ],
                        ],
                        [
                            'content' => [
                                'node' => '/jats:front/jats:article-meta/jats:article-categories/'
                                    .'jats:subj-group[1]/jats:subject[2]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['qux' => 'quux'],
                            ],
                        ],
                        [
                            'content' => [
                                'node' => '/jats:front/jats:article-meta/jats:article-categories/'
                                    .'jats:subj-group[2]/jats:subject',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['qux' => 'quux'],
                            ],
                        ],
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $newContext);
    }
}
