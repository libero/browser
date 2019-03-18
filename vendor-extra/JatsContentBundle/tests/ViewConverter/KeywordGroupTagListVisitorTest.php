<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use Libero\JatsContentBundle\ViewConverter\KeywordGroupTagListVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class KeywordGroupTagListVisitorTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_kwd_group_element(string $xml) : void
    {
        $visitor = new KeywordGroupTagListVisitor($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement($xml);

        $view = $visitor->visit($element, new View('@LiberoPatterns/tag-list.html.twig'));

        $this->assertSame('@LiberoPatterns/tag-list.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<kwd-group xmlns="http://example.com">foo</kwd-group>'];
        yield 'different element' => ['<kwd xmlns="http://jats.nlm.nih.gov">foo</kwd>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_tag_list_template() : void
    {
        $visitor = new KeywordGroupTagListVisitor($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<kwd-group xmlns="http://jats.nlm.nih.gov">
    <title>Foo</title>
    <kwd>Bar</kwd>
    <kwd>Baz</kwd>
</kwd-group>
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
    public function it_does_nothing_if_there_is_no_kwds() : void
    {
        $visitor = new KeywordGroupTagListVisitor($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement('<kwd-group xmlns="http://jats.nlm.nih.gov"><x>foo</x></kwd-group>');

        $view = $visitor->visit($element, new View('@LiberoPatterns/tag-list.html.twig'));

        $this->assertSame('@LiberoPatterns/tag-list.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_list_set() : void
    {
        $visitor = new KeywordGroupTagListVisitor($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<kwd-group xmlns="http://jats.nlm.nih.gov">
    <title>Foo</title>
    <kwd>Bar</kwd>
    <kwd>Baz</kwd>
</kwd-group>
XML
        );

        $view = $visitor->visit($element, new View('@LiberoPatterns/tag-list.html.twig', ['list' => 'qux']));

        $this->assertSame('@LiberoPatterns/tag-list.html.twig', $view->getTemplate());
        $this->assertSame(['list' => 'qux'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider listProvider
     */
    public function it_sets_the_title_and_list_arguments_for_a_group_with_a_title_and_kwds(
        string $xml,
        array $translationKeys,
        array $expectedArguments
    ) : void {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            [
                'translated' => 'es string',
            ],
            'es',
            'messages'
        );

        $visitor = new KeywordGroupTagListVisitor($this->createDumpingConverter(), $translator, $translationKeys);

        $element = $this->loadElement($xml);
        $context = ['lang' => 'es'];

        $view = $visitor->visit($element, new View('@LiberoPatterns/tag-list.html.twig', [], $context));

        $this->assertSame('@LiberoPatterns/tag-list.html.twig', $view->getTemplate());
        $this->assertEquals($expectedArguments, $view->getArguments());
        $this->assertSame(['lang' => 'es'], $view->getContext());
    }

    public function listProvider() : iterable
    {
        yield 'title' => [
            <<<XML
<jats:kwd-group xmlns:jats="http://jats.nlm.nih.gov">
    <jats:title>Foo</jats:title>
    <jats:kwd>Bar</jats:kwd>
    <jats:kwd>Baz</jats:kwd>
</jats:kwd-group>
XML
            ,
            [],
            [
                'title' => [
                    'node' => '/jats:kwd-group/jats:title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['lang' => 'es'],
                ],
                'list' => [
                    'items' => [
                        [
                            'content' => [
                                'node' => '/jats:kwd-group/jats:kwd[1]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ],
                        ],
                        [
                            'content' => [
                                'node' => '/jats:kwd-group/jats:kwd[2]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'type with matching translation key' => [
            <<<XML
<jats:kwd-group xmlns:jats="http://jats.nlm.nih.gov" kwd-group-type="foo">
    <jats:kwd>Bar</jats:kwd>
    <jats:kwd>Baz</jats:kwd>
</jats:kwd-group>
XML
            ,
            ['foo' => 'translated'],
            [
                'title' => [
                    'text' => 'es string',
                ],
                'list' => [
                    'items' => [
                        [
                            'content' => [
                                'node' => '/jats:kwd-group/jats:kwd[1]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ],
                        ],
                        [
                            'content' => [
                                'node' => '/jats:kwd-group/jats:kwd[2]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'type without matching translation key' => [
            <<<XML
<jats:kwd-group xmlns:jats="http://jats.nlm.nih.gov" kwd-group-type="not-foo">
    <jats:kwd>Bar</jats:kwd>
    <jats:kwd>Baz</jats:kwd>
</jats:kwd-group>
XML
            ,
            ['foo' => 'translated'],
            [],
        ];

        yield 'neither title nor type' => [
            <<<XML
<jats:kwd-group xmlns:jats="http://jats.nlm.nih.gov">
    <jats:kwd>Foo</jats:kwd>
    <jats:kwd>Bar</jats:kwd>
</jats:kwd-group>
XML
            ,
            [],
            [],
        ];

        yield 'no kwds' => [
            <<<XML
<jats:kwd-group xmlns:jats="http://jats.nlm.nih.gov" kwd-group-type="foo">
    <jats:title>Bar</jats:title>
    <jats:x>Baz</jats:x>
</jats:kwd-group>
XML
            ,
            [],
            [],
        ];
    }
}
