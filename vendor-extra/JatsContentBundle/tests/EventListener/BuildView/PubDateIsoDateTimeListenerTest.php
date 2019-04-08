<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\PubDateIsoDateTimeListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class PubDateIsoDateTimeListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_pub_date_element(string $xml) : void
    {
        $listener = new PubDateIsoDateTimeListener();

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/time.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/time.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<pub-date iso-8601-date="2000-01-02" xmlns="http://example.com"/>'];
        yield 'different element' => ['<date iso-8601-date="2000-01-02" xmlns="http://jats.nlm.nih.gov"/>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_time_template() : void
    {
        $listener = new PubDateIsoDateTimeListener();

        $element = $this->loadElement('<pub-date iso-8601-date="2000-01-02" xmlns="http://jats.nlm.nih.gov"/>');

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
    public function it_does_nothing_if_there_is_already_a_datetime_attribute_set() : void
    {
        $listener = new PubDateIsoDateTimeListener();

        $element = $this->loadElement('<pub-date iso-8601-date="2000-01-02" xmlns="http://jats.nlm.nih.gov"/>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/time.html.twig', ['attributes' => ['datetime' => '1999-12-31']])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/time.html.twig', $view->getTemplate());
        $this->assertSame(['attributes' => ['datetime' => '1999-12-31']], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_it_is_incomplete() : void
    {
        $listener = new PubDateIsoDateTimeListener();

        $element = $this->loadElement('<pub-date iso-8601-date="2000-01" xmlns="http://jats.nlm.nih.gov"/>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/time.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/time.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider validProvider
     */
    public function it_sets_the_datetime_attribute(string $time, string $expected) : void
    {
        $listener = new PubDateIsoDateTimeListener();

        $element = $this->loadElement("<pub-date iso-8601-date='{$time}' xmlns='http://jats.nlm.nih.gov'/>");

        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/time.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/time.html.twig', $view->getTemplate());
        $this->assertEquals(['attributes' => ['datetime' => $expected]], $view->getArguments());
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }

    public function validProvider() : iterable
    {
        yield 'date only' => ['2000-01-02', '2000-01-02'];
        yield 'with time' => ['2000-01-02T03:04:05', '2000-01-02'];
    }
}
