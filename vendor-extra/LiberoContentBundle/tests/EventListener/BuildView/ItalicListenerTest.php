<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\EventListener\BuildView;

use Libero\LiberoContentBundle\EventListener\BuildView\ItalicListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Event\ChooseTemplateEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ItalicListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider templateChoiceProvider
     */
    public function it_can_choose_a_template(string $xml, ?string $expected) : void
    {
        $listener = new ItalicListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new ChooseTemplateEvent($element);
        $listener->onChooseTemplate($event);

        $this->assertSame($expected, $event->getTemplate());
    }

    public function templateChoiceProvider() : iterable
    {
        yield 'bold element' => ['<italic xmlns="http://libero.pub">foo</italic>', '@LiberoPatterns/italic.html.twig'];
        yield 'different namespace' => ['<italic xmlns="http://example.com">foo</italic>', null];
        yield 'different element' => ['<bold xmlns="http://libero.pub">foo</bold>', null];
    }

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_italic_element(string $xml) : void
    {
        $listener = new ItalicListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/italic.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/italic.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<italic xmlns="http://example.com">foo</italic>'];
        yield 'different element' => ['<bold xmlns="http://libero.pub">foo</bold>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_italic_template() : void
    {
        $listener = new ItalicListener($this->createFailingConverter());

        $element = $this->loadElement('<i xmlns="http://libero.pub">foo</i>');

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
        $listener = new ItalicListener($this->createFailingConverter());

        $element = $this->loadElement('<italic xmlns="http://libero.pub">foo</italic>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/italic.html.twig', ['text' => 'bar']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/italic.html.twig', $view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_template_and_text_argument() : void
    {
        $listener = new ItalicListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<libero:italic xmlns:libero="http://libero.pub">
    foo <libero:bold>bar</libero:bold> baz
</libero:italic>
XML
        );

        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/italic.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/italic.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new TemplateView(
                        '',
                        ['node' => '/libero:italic/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new TemplateView(
                        '',
                        ['node' => '/libero:italic/libero:bold', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new TemplateView(
                        '',
                        ['node' => '/libero:italic/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
