<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\NameEasternListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;
use function is_string;

final class NameEasternListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_name_element(string $xml) : void
    {
        $listener = new NameEasternListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => [
            '<name xmlns="http://example.com" name-style="eastern"><given-names>foo</given-names></name>',
        ];
        yield 'different element' => ['<italic xmlns="http://jats.nlm.nih.gov">foo</italic>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_link_template() : void
    {
        $listener = new NameEasternListener($this->createFailingConverter());

        $element = $this->loadElement(
            '<name xmlns="http://jats.nlm.nih.gov" name-style="eastern"><given-names>foo</given-names></name>'
        );

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
     * @dataProvider nameStyleProvider
     */
    public function it_does_nothing_if_it_is_not_eastern_name_style(?string $nameStyle) : void
    {
        $listener = new NameEasternListener($this->createFailingConverter());

        $element = $this->loadElement('<name xmlns="http://jats.nlm.nih.gov"><given-names>foo</given-names></name>');
        if (is_string($nameStyle)) {
            $element->setAttribute('name-style', $nameStyle);
        }

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/link.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nameStyleProvider() : iterable
    {
        yield 'none' => [null];
        yield 'western' => ['western'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_text_set() : void
    {
        $listener = new NameEasternListener($this->createFailingConverter());

        $element = $this->loadElement(
            '<name xmlns="http://jats.nlm.nih.gov" name-style="eastern"><given-names>foo</given-names></name>'
        );

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
        $listener = new NameEasternListener($this->createDumpingConverter());

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
        yield 'minimum' => [
            <<<XML
<jats:name xmlns:jats="http://jats.nlm.nih.gov" name-style="eastern">
    <jats:given-names>given names</jats:given-names>
</jats:name>
XML
            ,
            [
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:name/jats:given-names',
                        'template' => '@LiberoPatterns/link.html.twig',
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
        ];

        yield 'all parts' => [
            <<<XML
<jats:name xmlns:jats="http://jats.nlm.nih.gov" name-style="eastern">
        <jats:surname>surname</jats:surname>
        <jats:given-names>given names</jats:given-names>
        <jats:prefix>prefix</jats:prefix>
        <jats:suffix>suffix</jats:suffix>
</jats:name>
XML
            ,
            [
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:name/jats:prefix',
                        'template' => '@LiberoPatterns/link.html.twig',
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                ' ',
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:name/jats:surname',
                        'template' => '@LiberoPatterns/link.html.twig',
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                ' ',
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:name/jats:given-names',
                        'template' => '@LiberoPatterns/link.html.twig',
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                ' ',
                new TemplateView(
                    '',
                    [
                        'node' => '/jats:name/jats:suffix',
                        'template' => '@LiberoPatterns/link.html.twig',
                        'context' => ['qux' => 'quux'],
                    ]
                ),
            ],
        ];
    }
}
