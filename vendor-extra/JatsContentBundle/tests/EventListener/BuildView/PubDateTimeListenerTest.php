<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\PubDateTimeListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\View;
use Locale;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class PubDateTimeListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_pub_date_element(string $xml) : void
    {
        $listener = new PubDateTimeListener();

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new View('@LiberoPatterns/time.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/time.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<pub-date xmlns="http://example.com"/>'];
        yield 'different element' => ['<date xmlns="http://jats.nlm.nih.gov"/>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_time_template() : void
    {
        $listener = new PubDateTimeListener();

        $element = $this->loadElement('<pub-date xmlns="http://jats.nlm.nih.gov"/>');

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
        $listener = new PubDateTimeListener();

        $element = $this->loadElement('<pub-date xmlns="http://jats.nlm.nih.gov"/>');

        $event = new BuildViewEvent(
            $element,
            new View('@LiberoPatterns/time.html.twig', ['attributes' => ['datetime' => '1999-12-31'], 'text' => 'foo'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/time.html.twig', $view->getTemplate());
        $this->assertSame(['attributes' => ['datetime' => '1999-12-31'], 'text' => 'foo'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_datetime_attribute() : void
    {
        $listener = new PubDateTimeListener();

        $element = $this->loadElement('<pub-date xmlns="http://jats.nlm.nih.gov"/>');

        $event = new BuildViewEvent($element, new View('@LiberoPatterns/time.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/time.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_no_lang_context() : void
    {
        $listener = new PubDateTimeListener();

        $element = $this->loadElement('<pub-date xmlns="http://jats.nlm.nih.gov"/>');

        $event = new BuildViewEvent(
            $element,
            new View('@LiberoPatterns/time.html.twig', ['attributes' => ['datetime' => '2000-01-02']])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/time.html.twig', $view->getTemplate());
        $this->assertEquals(['attributes' => ['datetime' => '2000-01-02']], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider validProvider
     */
    public function it_sets_the_text(?string $lang, string $datetime, string $expected) : void
    {
        Locale::setDefault('de');

        $listener = new PubDateTimeListener();

        $element = $this->loadElement('<pub-date xmlns="http://jats.nlm.nih.gov"/>');

        $context = ['lang' => $lang];

        $event = new BuildViewEvent(
            $element,
            new View('@LiberoPatterns/time.html.twig', ['attributes' => ['datetime' => $datetime]], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/time.html.twig', $view->getTemplate());
        $this->assertEquals(['attributes' => ['datetime' => $datetime], 'text' => $expected], $view->getArguments());
        $this->assertSame($context, $view->getContext());
    }

    public function validProvider() : iterable
    {
        yield 'en' => [
            'en',
            '2000-01-02',
            'Jan 2, 2000',
        ];

        yield 'fr' => [
            'fr',
            '2000-01-02',
            '2 janv. 2000',
        ];
    }
}
