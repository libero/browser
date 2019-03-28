<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\EventListener\BuildView\LangListener;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class LangListenerTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_lang() : void
    {
        $listener = new LangListener();

        $element = $this->loadElement('<foo><bar>baz</bar></foo>');

        $event = new BuildViewEvent($element, new View('template'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertFalse($view->hasArgument('attributes'));
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_lang_attribute() : void
    {
        $listener = new LangListener();

        $element = $this->loadElement('<foo xml:lang="fr">bar</foo>');

        $event = new BuildViewEvent($element, new View('template', ['attributes' => ['lang' => 'en']]));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame(['lang' => 'en'], $view->getArgument('attributes'));
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider contextProvider
     */
    public function it_sets_the_language_and_direction(
        string $xml,
        string $selector,
        ?array $expectedAttributes,
        array $context,
        array $expectedContext
    ) : void {
        $listener = new LangListener();

        $document = $this->loadDocument($xml);
        /** @var Element $element */
        $element = $document->xpath()->firstOf($selector);

        $event = new BuildViewEvent($element, new View('template', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame($expectedAttributes, $view->getArgument('attributes'), 'Attributes do not match');
        $this->assertSame($expectedContext, $view->getContext(), 'Context does not match');
    }

    public function contextProvider() : iterable
    {
        yield 'en with no context' => [
            '<foo xml:lang="en">bar</foo>',
            '/foo',
            ['lang' => 'en', 'dir' => 'ltr'],
            [],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'inheriting en with no context' => [
            '<foo xml:lang="ar"><bar xml:lang="en"><baz>qux</baz></bar></foo>',
            '/foo/bar/baz',
            ['lang' => 'en', 'dir' => 'ltr'],
            [],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'becoming en with no context' => [
            '<foo xml:lang="ar"><bar xml:lang="en">baz</bar></foo>',
            '/foo/bar',
            ['lang' => 'en', 'dir' => 'ltr'],
            [],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'en with en context' => [
            '<foo xml:lang="en">bar</foo>',
            '/foo',
            null,
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'inheriting en with en context' => [
            '<foo xml:lang="ar"><bar xml:lang="en"><baz>qux</baz></bar></foo>',
            '/foo/bar/baz',
            null,
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'becoming en with en context' => [
            '<foo xml:lang="ar"><bar xml:lang="en">baz</bar></foo>',
            '/foo/bar',
            null,
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'en with ar context' => [
            '<foo xml:lang="en">bar</foo>',
            '/foo',
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'inheriting en with ar context' => [
            '<foo xml:lang="ar"><bar xml:lang="en"><baz>qux</baz></bar></foo>',
            '/foo/bar/baz',
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'becoming en with ar context' => [
            '<foo xml:lang="ar"><bar xml:lang="en">baz</bar></foo>',
            '/foo/bar',
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'en with fr context' => [
            '<foo xml:lang="en">bar</foo>',
            '/foo',
            ['lang' => 'en'],
            ['lang' => 'fr', 'dir' => 'ltr'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'inheriting en with fr context' => [
            '<foo xml:lang="ar"><bar xml:lang="en"><baz>qux</baz></bar></foo>',
            '/foo/bar/baz',
            ['lang' => 'en'],
            ['lang' => 'fr', 'dir' => 'ltr'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'becoming en with fr context' => [
            '<foo xml:lang="ar"><bar xml:lang="en">baz</bar></foo>',
            '/foo/bar',
            ['lang' => 'en'],
            ['lang' => 'fr', 'dir' => 'ltr'],
            ['lang' => 'en', 'dir' => 'ltr'],
        ];
        yield 'ar with no context' => [
            '<foo xml:lang="ar">bar</foo>',
            '/foo',
            ['lang' => 'ar', 'dir' => 'rtl'],
            [],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'inheriting ar with no context' => [
            '<foo xml:lang="en"><bar xml:lang="ar"><baz>qux</baz></bar></foo>',
            '/foo/bar/baz',
            ['lang' => 'ar', 'dir' => 'rtl'],
            [],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'becoming ar with no context' => [
            '<foo xml:lang="en"><bar xml:lang="ar">baz</bar></foo>',
            '/foo/bar',
            ['lang' => 'ar', 'dir' => 'rtl'],
            [],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'ar with ar context' => [
            '<foo xml:lang="ar">bar</foo>',
            '/foo',
            null,
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'inheriting ar with ar context' => [
            '<foo xml:lang="en"><bar xml:lang="ar"><baz>qux</baz></bar></foo>',
            '/foo/bar/baz',
            null,
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'becoming ar with ar context' => [
            '<foo xml:lang="en"><bar xml:lang="ar">baz</bar></foo>',
            '/foo/bar',
            null,
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'ar with en context' => [
            '<foo xml:lang="ar">bar</foo>',
            '/foo',
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'inheriting ar with en context' => [
            '<foo xml:lang="en"><bar xml:lang="ar"><baz>qux</baz></bar></foo>',
            '/foo/bar/baz',
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'becoming ar with en context' => [
            '<foo xml:lang="en"><bar xml:lang="ar">baz</bar></foo>',
            '/foo/bar',
            ['lang' => 'ar', 'dir' => 'rtl'],
            ['lang' => 'en', 'dir' => 'ltr'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'ar with he context' => [
            '<foo xml:lang="ar">bar</foo>',
            '/foo',
            ['lang' => 'ar'],
            ['lang' => 'he', 'dir' => 'rtl'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'inheriting ar with he context' => [
            '<foo xml:lang="en"><bar xml:lang="ar"><baz>qux</baz></bar></foo>',
            '/foo/bar/baz',
            ['lang' => 'ar'],
            ['lang' => 'he', 'dir' => 'rtl'],
            ['lang' => 'ar', 'dir' => 'rtl'],
        ];
        yield 'becoming ar with he context' => [
            '<foo xml:lang="en"><bar xml:lang="ar">baz</bar></foo>',
            '/foo/bar',
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
        $listener = new LangListener();

        $element = $this->loadElement('<foo xml:lang="en">bar</foo>');
        $context = ['baz' => 'qux'];

        $event = new BuildViewEvent(
            $element,
            new View('template', ['attributes' => ['foo' => 'bar'], 'baz' => 'qux'], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame(
            ['attributes' => ['foo' => 'bar', 'lang' => 'en', 'dir' => 'ltr'], 'baz' => 'qux'],
            $view->getArguments()
        );
        $this->assertSame(
            ['baz' => 'qux', 'lang' => 'en', 'dir' => 'ltr'],
            $view->getContext()
        );
    }
}
