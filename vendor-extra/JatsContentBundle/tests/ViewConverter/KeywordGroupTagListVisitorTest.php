<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\JatsContentBundle\ViewConverter\KeywordGroupTagListVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;

final class KeywordGroupTagListVisitorTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_kwd_group_element(string $xml) : void
    {
        $visitor = new KeywordGroupTagListVisitor($this->createFailingConverter(), new IdentityTranslator());

        $xml = FluentDOM::load($xml);
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit($element, new View('@LiberoPatterns/tag-list.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/tag-list.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
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

        $xml = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<kwd-group xmlns="http://jats.nlm.nih.gov">
    <title>Foo</title>
    <kwd>Bar</kwd>
    <kwd>Baz</kwd>
</kwd-group>
XML
        );
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
    public function it_does_nothing_if_there_is_no_kwds() : void
    {
        $visitor = new KeywordGroupTagListVisitor($this->createFailingConverter(), new IdentityTranslator());

        $xml = FluentDOM::load('<kwd-group xmlns="http://jats.nlm.nih.gov"><x>foo</x></kwd-group>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/tag-list.html.twig'),
            $newContext
        );

        $this->assertSame('@LiberoPatterns/tag-list.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_list_set() : void
    {
        $visitor = new KeywordGroupTagListVisitor($this->createFailingConverter(), new IdentityTranslator());

        $xml = FluentDOM::load(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<kwd-group xmlns="http://jats.nlm.nih.gov">
    <title>Foo</title>
    <kwd>Bar</kwd>
    <kwd>Baz</kwd>
</kwd-group>
XML
        );
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/tag-list.html.twig', ['list' => 'qux']),
            $newContext
        );

        $this->assertSame('@LiberoPatterns/tag-list.html.twig', $view->getTemplate());
        $this->assertSame(['list' => 'qux'], $view->getArguments());
        $this->assertEmpty($newContext);
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

        $xml = FluentDOM::load($xml);
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['lang' => 'es'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/tag-list.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/tag-list.html.twig', $view->getTemplate());
        $this->assertEquals($expectedArguments, $view->getArguments());
        $this->assertSame(['lang' => 'es'], $newContext);
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
