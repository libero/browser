<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\FrontSubjectGroupTeaserListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontSubjectGroupTeaserListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $listener = new FrontSubjectGroupTeaserListener(
            $this->createFailingConverter(),
            new IdentityTranslator()
        );

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
        $listener = new FrontSubjectGroupTeaserListener(
            $this->createFailingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="heading">
                <subject>foo</subject>
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
    public function it_does_nothing_if_there_are_no_subject_groups() : void
    {
        $listener = new FrontSubjectGroupTeaserListener(
            $this->createFailingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="not-heading">
                <subject>foo</subject>
            </subj-group>
        </article-categories>
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
    public function it_does_nothing_if_there_is_already_categories_set() : void
    {
        $listener = new FrontSubjectGroupTeaserListener(
            $this->createFailingConverter(),
            new IdentityTranslator()
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <article-categories>
            <subj-group subj-group-type="heading">
                <subject>foo</subject>
            </subj-group>
        </article-categories>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/teaser.html.twig', ['categories' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/teaser.html.twig', $view->getTemplate());
        $this->assertSame(['categories' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_categories_argument() : void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            ['libero.patterns.teaser.categories.label' => 'categories label in es'],
            'es',
            'messages'
        );

        $listener = new FrontSubjectGroupTeaserListener($this->createDumpingConverter(), $translator);

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-meta>
        <jats:article-categories>
            <jats:subj-group subj-group-type="heading">
                <jats:subject>foo</jats:subject>
                <jats:subject>bar</jats:subject>
            </jats:subj-group>
            <jats:subj-group subj-group-type="heading">
                <jats:subject>baz</jats:subject>
            </jats:subj-group>
        </jats:article-categories>
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
                'categories' => [
                    'attributes' => [
                        'aria-label' => 'categories label in es',
                    ],
                    'items' => [
                        [
                            'content' => [
                                'node' => '/jats:front/jats:article-meta/jats:article-categories/'
                                    .'jats:subj-group[1]/jats:subject[1]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ],
                        ],
                        [
                            'content' => [
                                'node' => '/jats:front/jats:article-meta/jats:article-categories/'
                                    .'jats:subj-group[1]/jats:subject[2]',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
                            ],
                        ],
                        [
                            'content' => [
                                'node' => '/jats:front/jats:article-meta/jats:article-categories/'
                                    .'jats:subj-group[2]/jats:subject',
                                'template' => '@LiberoPatterns/link.html.twig',
                                'context' => ['lang' => 'es'],
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
