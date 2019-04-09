<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener\BuildView;

use Libero\LiberoPageBundle\EventListener\BuildView\ItemListTitleListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ItemListTitleListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_item_list_element(string $xml) : void
    {
        $listener = new ItemListTitleListener(new IdentityTranslator());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser-list.html.twig', [], ['list_title' => 'title_key'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser-list.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertSame(['list_title' => 'title_key'], $view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<item-list xmlns="http://example.com"/>'];
        yield 'different element' => ['<not-list xmlns="http://libero.pub"/>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_teaser_list_template() : void
    {
        $listener = new ItemListTitleListener(new IdentityTranslator());

        $element = $this->loadElement('<item-list xmlns="http://libero.pub"/>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView('template', [], ['list_title' => 'title_key'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertSame(['list_title' => 'title_key'], $view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_title_argument() : void
    {
        $listener = new ItemListTitleListener(new IdentityTranslator());

        $element = $this->loadElement('<item-list xmlns="http://libero.pub"/>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser-list.html.twig', ['title' => 'foo'], ['list_title' => 'title_key'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser-list.html.twig', $view->getTemplate());
        $this->assertSame(['title' => 'foo'], $view->getArguments());
        $this->assertSame(['list_title' => 'title_key'], $view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_list_title_context() : void
    {
        $listener = new ItemListTitleListener(new IdentityTranslator());

        $element = $this->loadElement('<item-list xmlns="http://libero.pub"/>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/teaser-list.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser-list.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_title_argument() : void
    {
        $translator = new Translator('es');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            ['title_key' => 'title_key in es'],
            'es',
            'messages'
        );

        $listener = new ItemListTitleListener($translator);

        $element = $this->loadElement('<item-list xmlns="http://libero.pub"/>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser-list.html.twig', [], ['lang' => 'es', 'list_title' => 'title_key'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame(['title' => ['level' => 1, 'text' => 'title_key in es']], $view->getArguments());
        $this->assertSame(['lang' => 'es', 'list_title' => 'title_key', 'level' => 2], $view->getContext());
    }
}
