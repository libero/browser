<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\DatePartsListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class DatePartsListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_pub_date_element(string $xml) : void
    {
        $listener = new DatePartsListener();

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new View('@LiberoPatterns/date.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/date.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => [
            <<<XML
<pub-date xmlns="http://example.com">
    <year>2000</year>
    <month>1</month>
    <day>2</day>
</pub-date>
XML
            ,
        ];
        yield 'different element' => [
            <<<XML
<date xmlns="http://jats.nlm.nih.gov">
    <year>2000</year>
    <month>1</month>
    <day>2</day>
</date>
XML
            ,
        ];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_date_template() : void
    {
        $listener = new DatePartsListener();

        $element = $this->loadElement(
            <<<XML
<pub-date xmlns="http://jats.nlm.nih.gov">
    <year>2000</year>
    <month>1</month>
    <day>2</day>
</pub-date>
XML
        );

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
    public function it_does_nothing_if_there_is_already_a_date_set() : void
    {
        $listener = new DatePartsListener();

        $element = $this->loadElement(
            <<<XML
<pub-date xmlns="http://jats.nlm.nih.gov">
    <year>2000</year>
    <month>1</month>
    <day>2</day>
</pub-date>
XML
        );

        $event = new BuildViewEvent($element, new View('@LiberoPatterns/date.html.twig', ['date' => '1999-12-31']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/date.html.twig', $view->getTemplate());
        $this->assertSame(['date' => '1999-12-31'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_a_part_is_missing() : void
    {
        $listener = new DatePartsListener();

        $element = $this->loadElement(
            <<<XML
<pub-date xmlns="http://jats.nlm.nih.gov">
    <month>1</month>
    <day>2</day>
</pub-date>
XML
        );

        $event = new BuildViewEvent($element, new View('@LiberoPatterns/date.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/date.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     * @dataProvider partsProvider
     */
    public function it_sets_the_date_argument(string $xml, string $expected) : void
    {
        $listener = new DatePartsListener();

        $element = $this->loadElement($xml);

        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new View('@LiberoPatterns/date.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/date.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'date' => $expected,
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }

    public function partsProvider() : iterable
    {
        yield 'basic' => [
            <<<XML
<pub-date xmlns="http://jats.nlm.nih.gov">
    <year>2000</year>
    <month>1</month>
    <day>2</day>
</pub-date>
XML
            ,
            '2000-01-02',
        ];

        yield 'different order' => [
            <<<XML
<pub-date xmlns="http://jats.nlm.nih.gov">
    <day>2</day>
    <year>2000</year>
    <month>1</month>
</pub-date>
XML
            ,
            '2000-01-02',
        ];

        yield 'other elements' => [
            <<<XML
<pub-date xmlns="http://jats.nlm.nih.gov">
    <year>Two Thousand</year>
    <year>2000</year>
    <year>1999</year>
    <month>January</month>
    <month>1</month>
    <month>12</month>
    <day>Second</day>
    <day>2</day>
    <day>31</day>
</pub-date>
XML
            ,
            '2000-01-02',
        ];
    }
}
