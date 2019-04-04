<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\FrontArticleTypeContentMetaListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontArticleTypeContentMetaListenerTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $listener = new FrontArticleTypeContentMetaListener(new IdentityTranslator());

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
        $listener = new FrontArticleTypeContentMetaListener(
            new IdentityTranslator(),
            ['research-article' => 'Research article']
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<article article-type="research-article" xmlns="http://jats.nlm.nih.gov">
    <front/>
</article>
XML
        )->firstElementChild;

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
    public function it_does_nothing_if_it_does_have_have_the_article_as_a_parent() : void
    {
        $listener = new FrontArticleTypeContentMetaListener(
            new IdentityTranslator(),
            ['research-article' => 'Research article']
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov"/>
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
    public function it_does_nothing_if_there_is_no_article_type() : void
    {
        $listener = new FrontArticleTypeContentMetaListener(
            new IdentityTranslator(),
            ['research-article' => 'Research article']
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://jats.nlm.nih.gov">
    <front/>
</article>
XML
        )->firstElementChild;

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
        $listener = new FrontArticleTypeContentMetaListener(
            new IdentityTranslator(),
            ['research-article' => 'Research article']
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<article article-type="research-article" xmlns="http://jats.nlm.nih.gov">
    <front/>
</article>
XML
        )->firstElementChild;

        $event = new BuildViewEvent(
            $element,
            new TemplateView(
                '@LiberoPatterns/content-meta.html.twig',
                ['items' => ['type' => 'foo']]
            )
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
    public function it_does_nothing_if_the_type_is_unknown() : void
    {
        $listener = new FrontArticleTypeContentMetaListener(
            new IdentityTranslator(),
            ['research-article' => 'Research article']
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:article article-type="not-research-article" xmlns:jats="http://jats.nlm.nih.gov">
    <jats:front/>
</jats:article>
XML
        )->firstElementChild;

        $context = ['bar' => 'baz'];

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/content-meta.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-meta.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertSame(['bar' => 'baz'], $view->getContext());
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
            ['libero.patterns.content_header.meta.type.label' => 'type label in es'],
            'es',
            'messages'
        );

        $listener = new FrontArticleTypeContentMetaListener(
            $translator,
            ['research-article' => 'Artículo de investigación']
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:article article-type="research-article" xmlns:jats="http://jats.nlm.nih.gov">
    <jats:front/>
</jats:article>
XML
        )->firstElementChild;

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
                            'text' => 'Artículo de investigación',
                        ],
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['lang' => 'es'], $view->getContext());
    }
}
