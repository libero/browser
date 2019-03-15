<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use Libero\JatsContentBundle\ViewConverter\FrontSubjectGroupContentHeaderVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
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
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement($xml);

        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig'));

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
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
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter(), new IdentityTranslator());

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

        $view = $visitor->visit($element, new View('template'));

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_are_no_subject_groups() : void
    {
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter(), new IdentityTranslator());

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

        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig'));

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_categories_set() : void
    {
        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createFailingConverter(), new IdentityTranslator());

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

        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/content-header.html.twig', ['categories' => 'bar'])
        );

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertSame(['categories' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_categories_argument() : void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            ['libero.patterns.content_header.categories.label' => 'categories label in es'],
            'es',
            'messages'
        );

        $visitor = new FrontSubjectGroupContentHeaderVisitor($this->createDumpingConverter(), $translator);

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

        $context = ['lang' => 'es'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig', [], $context));

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'categories' => [
                    'attributes' => [
                        'aria-label' => 'categories label in es',
                    ],
                    'items' => [
                        [
                            'content' => [
                                'node' => '/jats:front/jats:article-meta/jats:article-categories/'
                                    .'jats:subj-group[1]/jats:subject[1]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ],
                        ],
                        [
                            'content' => [
                                'node' => '/jats:front/jats:article-meta/jats:article-categories/'
                                    .'jats:subj-group[1]/jats:subject[2]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ],
                        ],
                        [
                            'content' => [
                                'node' => '/jats:front/jats:article-meta/jats:article-categories/'
                                    .'jats:subj-group[2]/jats:subject',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ],
                        ],
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['lang' => 'es'], $view->getContext());
    }
}
