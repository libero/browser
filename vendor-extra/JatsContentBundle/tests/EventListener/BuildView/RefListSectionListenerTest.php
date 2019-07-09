<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\RefListSectionListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class RefListSectionListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_ref_list_element(string $xml) : void
    {
        $listener = new RefListSectionListener($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/section.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/section.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<ref-list xmlns="http://example.com">foo</ref-list>'];
        yield 'different element' => ['<p xmlns="http://jats.nlm.nih.gov">foo</p>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_section_template() : void
    {
        $listener = new RefListSectionListener($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement('<ref-list xmlns="http://jats.nlm.nih.gov">foo</ref-list>');

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
        $listener = new RefListSectionListener($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement('<ref-list xmlns="http://jats.nlm.nih.gov">foo</ref-list>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/section.html.twig', ['content' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/section.html.twig', $view->getTemplate());
        $this->assertSame(['content' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider contentProvider
     */
    public function it_sets_the_heading_and_content_arguments(string $xml, array $expectedArguments) : void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            ['libero.page.references' => 'es string'],
            'es',
            'messages'
        );

        $listener = new RefListSectionListener($this->createDumpingConverter(), $translator);

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/section.html.twig', [], ['lang' => 'es'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/section.html.twig', $view->getTemplate());
        $this->assertEquals($expectedArguments, $view->getArguments());
        $this->assertSame(['lang' => 'es', 'level' => 1], $view->getContext());
    }

    public function contentProvider() : iterable
    {
        yield 'no heading' => [
            <<<XML
<jats:ref-list xmlns:jats="http://jats.nlm.nih.gov"/>
XML
            ,
            [
                'heading' => [
                    'text' => 'es string',
                    'level' => 1,
                ],
                'content' => new TemplateView(
                    '',
                    [
                        'node' => '/jats:ref-list',
                        'template' => '@LiberoPatterns/reference-list.html.twig',
                        'context' => ['lang' => 'es', 'level' => 2],
                    ]
                ),
            ],
        ];

        yield 'heading' => [
            <<<XML
<jats:ref-list xmlns:jats="http://jats.nlm.nih.gov">
    <jats:title>foo</jats:title>
</jats:ref-list>
XML
            ,
            [
                'heading' => [
                    'node' => '/jats:ref-list/jats:title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['lang' => 'es', 'level' => 1],
                ],
                'content' => new TemplateView(
                    '',
                    [
                        'node' => '/jats:ref-list',
                        'template' => '@LiberoPatterns/reference-list.html.twig',
                        'context' => ['lang' => 'es', 'level' => 2],
                    ]
                ),
            ],
        ];
    }

    /**
     * @test
     */
    public function it_uses_the_current_level() : void
    {
        $listener = new RefListSectionListener($this->createDumpingConverter(), new IdentityTranslator());

        $element = $this->loadElement(
            <<<XML
<jats:ref-list xmlns:jats="http://jats.nlm.nih.gov">
    <jats:title>Title</jats:title>
</jats:ref-list>
XML
        );

        $context = ['level' => 3];

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/section.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/section.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'heading' => [
                    'node' => '/jats:ref-list/jats:title',
                    'template' => '@LiberoPatterns/heading.html.twig',
                    'context' => ['level' => 3],
                ],
                'content' => new TemplateView(
                    '',
                    [
                        'node' => '/jats:ref-list',
                        'template' => '@LiberoPatterns/reference-list.html.twig',
                        'context' => ['level' => 4],
                    ]
                ),
            ],
            $view->getArguments()
        );
        $this->assertSame($context, $view->getContext());
    }
}
