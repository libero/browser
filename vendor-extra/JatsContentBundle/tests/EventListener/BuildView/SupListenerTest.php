<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\SupListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class SupListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_sup_element(string $xml) : void
    {
        $listener = new SupListener($this->createFailingConverter());

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
        yield 'different namespace' => ['<sup xmlns="http://example.com">foo</sup>'];
        yield 'different element' => ['<bold xmlns="http://jats.nlm.nih.gov">foo</bold>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_sup_template() : void
    {
        $listener = new SupListener($this->createFailingConverter());

        $element = $this->loadElement('<sup xmlns="http://jats.nlm.nih.gov">foo</sup>');

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
        $listener = new SupListener($this->createFailingConverter());

        $element = $this->loadElement('<sup xmlns="http://jats.nlm.nih.gov">foo</sup>');

        $event = new BuildViewEvent($element, new TemplateView(null, ['text' => 'bar']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertNull($view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_template_and_text_argument() : void
    {
        $listener = new SupListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:sup xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:bold>bar</jats:bold> baz
</jats:sup>
XML
        );

        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new TemplateView(null, [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/sup.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new TemplateView(
                        null,
                        ['node' => '/jats:sup/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new TemplateView(
                        null,
                        ['node' => '/jats:sup/jats:bold', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new TemplateView(
                        null,
                        ['node' => '/jats:sup/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
