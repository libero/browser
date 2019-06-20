<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener\BuildView;

use Libero\LiberoPageBundle\EventListener\BuildView\ContentHeaderAffiliationDuplicateListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class ContentHeaderAffiliationDuplicateListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_not_the_content_header_template() : void
    {
        $listener = new ContentHeaderAffiliationDuplicateListener();

        $element = $this->loadElement('<foo/>');

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
    public function it_does_nothing_if_there_is_no_affiliations_argument() : void
    {
        $listener = new ContentHeaderAffiliationDuplicateListener();

        $element = $this->loadElement('<foo/>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/content-header.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_de_duplications_the_affiliations_argument() : void
    {
        $listener = new ContentHeaderAffiliationDuplicateListener();

        $element = $this->loadElement('<foo/>');

        $event = new BuildViewEvent(
            $element,
            new TemplateView(
                '@LiberoPatterns/content-header.html.twig',
                ['affiliations' => ['items' => ['one', 'one', 'two', 'one', 'three', 'two']]],
                ['lang' => 'es', 'list_empty' => 'empty_key']
            )
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame(['affiliations' => ['items' => ['one', 'two', 'three']]], $view->getArguments());
    }
}
