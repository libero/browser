<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\FrontDisplayChannelContentMetaListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontDisplayChannelContentMetaListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $listener = new FrontDisplayChannelContentMetaListener(
            $this->createDumpingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/content-meta.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-meta.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<front xmlns="http://example.com">foo</front>'];
        yield 'different element' => ['<foo xmlns="http://jats.nlm.nih.gov">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_content_meta_template() : void
    {
        $listener = new FrontDisplayChannelContentMetaListener(
            $this->createDumpingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="display-channel">
                <subject>Display Channel</subject>
            </subj-group>
        </article-categories>
    </article-meta>
</front>
XML
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
     */
    public function it_does_nothing_if_there_is_no_display_channel() : void
    {
        $listener = new FrontDisplayChannelContentMetaListener(
            $this->createDumpingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="display-channel"/>
        </article-categories>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/content-meta.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-meta.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_a_type_set() : void
    {
        $listener = new FrontDisplayChannelContentMetaListener(
            $this->createDumpingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="display-channel">
                <subject>Display Channel</subject>
            </subj-group>
        </article-categories>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/content-meta.html.twig', ['items' => ['type' => 'foo']])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-meta.html.twig', $view->getTemplate());
        $this->assertSame(['items' => ['type' => 'foo']], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_text_argument() : void
    {
        $translator = new Translator('es');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            ['libero.patterns.meta.type.label' => 'type label in es'],
            'es',
            'messages'
        );

        $listener = new FrontDisplayChannelContentMetaListener($this->createDumpingConverter(), $translator);

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-meta>
        <jats:article-categories>
            <jats:subj-group subj-group-type="display-channel">
                <jats:subject>Display Channel</jats:subject>
                <jats:subject>Display Channel 2</jats:subject>
            </jats:subj-group>
            <jats:subj-group subj-group-type="display-channel">
                <jats:subject>Display Channel 3</jats:subject>
            </jats:subj-group>
        </jats:article-categories>
    </jats:article-meta>
</jats:front>
XML
        );

        $context = ['lang' => 'es'];

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/content-meta.html.twig', ['items' => ['foo' => 'bar']], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-meta.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'items' => [
                    'foo' => 'bar',
                    'type' => [
                        'attributes' => [
                            'aria-label' => 'type label in es',
                        ],
                        'content' => [
                            'node' => '/jats:front/jats:article-meta/jats:article-categories'
                                .'/jats:subj-group[1]/jats:subject[1]',
                            'template' => '@LiberoPatterns/link.html.twig',
                            'context' => ['lang' => 'es'],
                        ],
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['lang' => 'es'], $view->getContext());
    }
}
