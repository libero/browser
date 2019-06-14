<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\LinkListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class LinkListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_link_template() : void
    {
        $listener = new LinkListener($this->createFailingConverter());

        $element = $this->loadElement('<ext-link xmlns="http://jats.nlm.nih.gov">foo</ext-link>');

        $event = new BuildViewEvent($element, new TemplateView('template'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_text_set() : void
    {
        $listener = new LinkListener($this->createFailingConverter());

        $element = $this->loadElement('<ext-link xmlns="http://jats.nlm.nih.gov">foo</ext-link>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig', ['text' => 'bar']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider textProvider
     */
    public function it_sets_the_text_argument(string $xml, array $expectedText) : void
    {
        $listener = new LinkListener($this->createDumpingConverter());

        $element = $this->loadElement($xml);

        $context = ['qux' => 'quux'];
        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEquals(['text' => $expectedText], $view->getArguments());
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }

    public function textProvider() : iterable
    {
        yield 'aff' => [
            <<<XML
<jats:aff xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:aff>
XML
            ,
            [
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:aff/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:aff/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:aff/text()[2]',
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
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:kwd/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:kwd/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:kwd/text()[2]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
        ];

        yield 'subject' => [
            <<<XML
<jats:subject xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:subject>
XML
            ,
            [
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:subject/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:subject/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    '',
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
    public function it_can_strip_inline_elements() : void
    {
        $listener = new LinkListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:aff xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:aff>
XML
        );

        $context = ['strip_elements' => true];
        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEquals(['text' => 'foo bar baz'], $view->getArguments());
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_it_is_something_else() : void
    {
        $listener = new LinkListener($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }
}
