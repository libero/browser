<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPatternsBundle\ViewConverter\Inline;

use FluentDOM;
use FluentDOM\DOM\Element;
use FluentDOM\DOM\Text;
use Libero\LiberoPatternsBundle\ViewConverter\Inline\LangVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class LangVisitorTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_nothing_if_it_is_not_an_element() : void
    {
        $visitor = new LangVisitor();

        $xml = FluentDOM::load('<foo>bar</foo>');
        /** @var Text $node */
        $node = $xml->documentElement->firstChild;

        $newContext = [];
        $view = $visitor->visit($node, new View('template'), $newContext);

        $this->assertEmpty($view->getArgument('attributes'));
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_xml_lang_attribute() : void
    {
        $visitor = new LangVisitor();

        $xml = FluentDOM::load('<foo>bar</foo>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit($element, new View('template'), $newContext);

        $this->assertEmpty($view->getArgument('attributes'));
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_lang_attribute() : void
    {
        $visitor = new LangVisitor();

        $xml = FluentDOM::load('<foo xml:lang="fr">bar</foo>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit($element, new View('template', ['attributes' => ['lang' => 'en']]), $newContext);

        $this->assertSame(['lang' => 'en'], $view->getArgument('attributes'));
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     * @dataProvider contextProvider
     */
    public function it_sets_the_language_and_direction(
        string $xml,
        array $expectedAttributes,
        array $context,
        array $expectedContext
    ) : void {
        $visitor = new LangVisitor();

        $xml = FluentDOM::load($xml);
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = $context;
        $view = $visitor->visit($element, new View('template'), $newContext);

        $this->assertSame($expectedAttributes, $view->getArgument('attributes'), 'Attributes do not match');
        $this->assertSame($expectedContext, $newContext, 'Context does not match');
    }

    public function contextProvider() : iterable
    {
        yield 'en with no context' => [
            '<foo xml:lang="en">bar</foo>',
            ['lang' => 'en', 'dir' => 'ltr'],
            [],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'en with en context' => [
            '<foo xml:lang="en">bar</foo>',
            [],
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'en with ar context' => [
            '<foo xml:lang="en">bar</foo>',
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'en with fr context' => [
            '<foo xml:lang="en">bar</foo>',
            ['lang' => 'en'],
            ['lang' => 'fr', 'dir' => 'ltr'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'ar with no context' => [
            '<foo xml:lang="ar">bar</foo>',
            ['lang' => 'ar', 'dir' => 'rtl'],
            [],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'ar with ar context' => [
            '<foo xml:lang="ar">bar</foo>',
            [],
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'ar with en context' => [
            '<foo xml:lang="ar">bar</foo>',
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'ar with he context' => [
            '<foo xml:lang="ar">bar</foo>',
            ['lang' => 'ar'],
            ['lang' => 'he', 'dir' => 'rtl'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
    }

    /**
     * @test
     */
    public function it_leaves_other_arguments_and_context() : void
    {
        $visitor = new LangVisitor();

        $xml = FluentDOM::load('<foo xml:lang="en">bar</foo>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['foo' => 'bar'];
        $view = $visitor->visit(
            $element,
            new View('template', ['attributes' => ['foo' => 'bar'], 'foo' => 'bar']),
            $newContext
        );

        $this->assertSame(
            ['attributes' => ['foo' => 'bar', 'lang' => 'en', 'dir' => 'ltr'], 'foo' => 'bar'],
            $view->getArguments()
        );
        $this->assertSame(
            ['foo' => 'bar', 'lang' => 'en', 'dir' => 'ltr'],
            $newContext
        );
    }
}
