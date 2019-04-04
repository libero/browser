<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\SectionListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class SectionListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_sec_element(string $xml) : void
    {
        $listener = new SectionListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView(null));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<sec xmlns="http://example.com">foo</sec>'];
        yield 'different element' => ['<p xmlns="http://jats.nlm.nih.gov">foo</p>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_section_template() : void
    {
        $listener = new SectionListener($this->createFailingConverter());

        $element = $this->loadElement('<sec xmlns="http://jats.nlm.nih.gov">foo</sec>');

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
    public function it_does_nothing_if_there_is_already_content_set() : void
    {
        $listener = new SectionListener($this->createFailingConverter());

        $element = $this->loadElement('<sec xmlns="http://jats.nlm.nih.gov">foo</sec>');

        $event = new BuildViewEvent($element, new TemplateView(null, ['content' => 'bar']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertNull($view->getTemplate());
        $this->assertSame(['content' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider contentProvider
     */
    public function it_sets_the_heading_and_content_arguments(string $xml, array $expectedArguments) : void
    {
        $listener = new SectionListener($this->createDumpingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView(null));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/section.html.twig', $view->getTemplate());
        $this->assertEquals($expectedArguments, $view->getArguments());
        $this->assertSame(['level' => 1], $view->getContext());
    }

    public function contentProvider() : iterable
    {
        yield 'no heading' => [
            <<<XML
<jats:sec xmlns:jats="http://jats.nlm.nih.gov">
    <jats:p>foo</jats:p>
    <jats:p>bar</jats:p>
</jats:sec>
XML
            ,
            [
                'content' => [
                    new TemplateView(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[1]',
                            'template' => null,
                            'context' => ['level' => 2],
                        ]
                    ),
                    new TemplateView(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[2]',
                            'template' => null,
                            'context' => ['level' => 2],
                        ]
                    ),
                ],
            ],
        ];

        yield 'heading' => [
            <<<XML
<jats:sec xmlns:jats="http://jats.nlm.nih.gov">
    <jats:title>foo</jats:title>
    <jats:p>bar</jats:p>
    <jats:p>baz</jats:p>
</jats:sec>
XML
            ,
            [
                'heading' => [
                    'node' => '/jats:sec/jats:title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['level' => 1],
                ],
                'content' => [
                    new TemplateView(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[1]',
                            'template' => null,
                            'context' => ['level' => 2],
                        ]
                    ),
                    new TemplateView(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[2]',
                            'template' => null,
                            'context' => ['level' => 2],
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_uses_the_current_level() : void
    {
        $listener = new SectionListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:sec xmlns:jats="http://jats.nlm.nih.gov">
    <jats:p>foo</jats:p>
    <jats:p>bar</jats:p>
</jats:sec>
XML
        );

        $context = ['level' => 3];

        $event = new BuildViewEvent($element, new TemplateView(null, [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/section.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'content' => [
                    new TemplateView(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[1]',
                            'template' => null,
                            'context' => ['level' => 4],
                        ]
                    ),
                    new TemplateView(
                        null,
                        [
                            'node' => '/jats:sec/jats:p[2]',
                            'template' => null,
                            'context' => ['level' => 4],
                        ]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame($context, $view->getContext());
    }
}
