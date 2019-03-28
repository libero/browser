<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\LinkListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

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

        $event = new BuildViewEvent($element, new View('template'));
        $listener->onBuildView($event);
        $view = $event->getView();

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

        $event = new BuildViewEvent($element, new View(null, ['text' => 'bar']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertNull($view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider textProvider
     */
    public function it_sets_the_template_and_text_argument(string $xml, array $expectedText) : void
    {
        $listener = new LinkListener($this->createDumpingConverter());

        $element = $this->loadElement($xml);

        $context = ['qux' => 'quux'];
        $event = new BuildViewEvent($element, new View('@LiberoPatterns/link.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEquals(['text' => $expectedText], $view->getArguments());
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }

    public function textProvider() : iterable
    {
        yield 'kwd' => [
            <<<XML
<jats:kwd xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz
</jats:kwd>
XML
            ,
            [
                new View(
                    null,
                    [
                        'node' => '/jats:kwd/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:kwd/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
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
                new View(
                    null,
                    [
                        'node' => '/jats:subject/text()[1]',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
                    [
                        'node' => '/jats:subject/jats:italic',
                        'template' => null,
                        'context' => ['qux' => 'quux'],
                    ]
                ),
                new View(
                    null,
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
    public function it_does_nothing_if_it_is_something_else() : void
    {
        $listener = new LinkListener($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

        $event = new BuildViewEvent($element, new View('@LiberoPatterns/link.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/link.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }
}
