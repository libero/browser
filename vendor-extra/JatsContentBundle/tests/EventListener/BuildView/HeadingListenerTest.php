<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\HeadingListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class HeadingListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_heading_template() : void
    {
        $listener = new HeadingListener($this->createFailingConverter());

        $element = $this->loadElement('<article-title xmlns="http://jats.nlm.nih.gov">foo</article-title>');

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
        $listener = new HeadingListener($this->createFailingConverter());

        $element = $this->loadElement('<article-title xmlns="http://jats.nlm.nih.gov">foo</article-title>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/heading.html.twig', ['text' => 'bar']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider textProvider
     */
    public function it_sets_the_text_argument(string $xml, array $expectedText) : void
    {
        $listener = new HeadingListener($this->createDumpingConverter());

        $element = $this->loadElement($xml);
        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/heading.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertEquals(['text' => $expectedText], $view->getArguments());
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }

    public function textProvider() : iterable
    {
        yield 'article-title' => [
            <<<XML
<jats:article-title xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:article-title>
XML
            ,
            [
                new TemplateView(
                    null,
                    [
                        'node' => '/jats:article-title/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    null,
                    [
                        'node' => '/jats:article-title/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    null,
                    [
                        'node' => '/jats:article-title/text()[2]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
        ];

        yield 'title' => [
            <<<XML
<jats:title xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:title>
XML
            ,
            [
                new TemplateView(
                    null,
                    [
                        'node' => '/jats:title/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    null,
                    [
                        'node' => '/jats:title/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new TemplateView(
                    null,
                    [
                        'node' => '/jats:title/text()[2]',
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
    public function it_does_nothing_if_it_is_something_else() : void
    {
        $listener = new HeadingListener($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/heading.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }
}
