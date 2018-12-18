<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPatternsBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\LiberoPatternsBundle\ViewConverter\LangVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;

final class LangVisitorTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_visits_the_lang_attribute(
        string $xml,
        array $expected = [],
        array $context = [],
        array $expectedContext = []
    ) : void {
        $visitor = new LangVisitor();

        $xml = FluentDOM::load($xml);
        $xml->namespaces();
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = $context;
        $view = $visitor->visit($element, new View('template'), $newContext);

        $this->assertSame($expected, $view->getArgument('attributes'), 'Attributes do not match');
        $this->assertSame($expectedContext, $newContext, 'Context does not match');
    }

    public function dataProvider() : iterable
    {
        yield 'no lang attribute' => [
            '<foo>bar</foo>',
        ];
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
}
