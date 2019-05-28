<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\FrontPubDateContentMetaListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontPubDateContentMetaListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $listener = new FrontPubDateContentMetaListener(
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
        yield 'different element' => ['<back xmlns="http://jats.nlm.nih.gov">foo</back>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_content_meta_template() : void
    {
        $listener = new FrontPubDateContentMetaListener(
            $this->createDumpingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <pub-date date-type="pub"/>
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
    public function it_does_nothing_if_there_is_no_pub_date() : void
    {
        $listener = new FrontPubDateContentMetaListener(
            $this->createDumpingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <pub-date date-type="not-pub"/>
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
    public function it_does_nothing_if_there_is_already_a_date_set() : void
    {
        $listener = new FrontPubDateContentMetaListener(
            $this->createDumpingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <pub-date date-type="pub"/>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/content-meta.html.twig', ['items' => ['date' => 'foo']])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-meta.html.twig', $view->getTemplate());
        $this->assertSame(['items' => ['date' => 'foo']], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_date_argument() : void
    {
        $translator = new Translator('es');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            ['libero.patterns.content_header.meta.date.label' => 'date label in es'],
            'es',
            'messages'
        );

        $listener = new FrontPubDateContentMetaListener($this->createDumpingConverter(), $translator);

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-meta>
        <jats:pub-date date-type="not-pub"/>
        <jats:pub-date date-type="pub"/>
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
                    'date' => [
                        'attributes' => [
                            'aria-label' => 'date label in es',
                        ],
                        'content' => [
                            'text' => [
                                new TemplateView(
                                    '',
                                    [
                                        'node' => '/jats:front/jats:article-meta/jats:pub-date[2]',
                                        'template' => '@LiberoPatterns/time.html.twig',
                                        'context' => ['lang' => 'es'],
                                        'format' => 'medium',
                                    ]
                                ),
                            ],
                        ],
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['lang' => 'es'], $view->getContext());
    }
}
