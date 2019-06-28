<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\FrontContribAuthorTeaserListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontContribAuthorTeaserListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $listener = new FrontContribAuthorTeaserListener($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/teaser.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
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
    public function it_does_nothing_if_is_not_the_teaser_template() : void
    {
        $listener = new FrontContribAuthorTeaserListener($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <contrib-group>
            <contrib contrib-type="author"/>
        </contrib-group>
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
    public function it_does_nothing_if_there_is_no_author_contrib() : void
    {
        $listener = new FrontContribAuthorTeaserListener($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <contrib-group>
            <contrib contrib-type="not-author"/>
        </contrib-group>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/teaser.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_details_set() : void
    {
        $listener = new FrontContribAuthorTeaserListener($this->createFailingConverter(), new IdentityTranslator());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <contrib-group>
            <contrib contrib-type="author"/>
        </contrib-group>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser.html.twig', ['details' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertSame(['details' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_details_argument() : void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            ['libero.patterns.teaser.authors' => '{count} {author1} {author2} {author3}'],
            'es',
            'messages'
        );

        $listener = new FrontContribAuthorTeaserListener($this->createDumpingConverter(), $translator);

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-meta>
        <jats:contrib-group>
            <jats:contrib contrib-type="author"><jats:name/></jats:contrib>
            <jats:contrib contrib-type="author"/>
            <jats:contrib contrib-type="author"><jats:name/></jats:contrib>
            <jats:contrib contrib-type="not-author"><jats:name/></jats:contrib>
        </jats:contrib-group>
        <jats:contrib-group>
            <jats:contrib/>
            <jats:contrib contrib-type="author"><jats:name/></jats:contrib>
        </jats:contrib-group>
    </jats:article-meta>
</jats:front>
XML
        );

        $context = ['lang' => 'es'];

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser.html.twig', [], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'details' => [
                    'text' => [
                        '3 ',
                        new TemplateView(
                            '',
                            [
                                'node' => '/jats:front/jats:article-meta/jats:contrib-group[1]/jats:contrib[1]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ]
                        ),
                        ' ',
                        new TemplateView(
                            '',
                            [
                                'node' => '/jats:front/jats:article-meta/jats:contrib-group[1]/jats:contrib[3]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ]
                        ),
                        ' ',
                        new TemplateView(
                            '',
                            [
                                'node' => '/jats:front/jats:article-meta/jats:contrib-group[2]/jats:contrib[2]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ]
                        ),
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['lang' => 'es'], $view->getContext());
    }
}
