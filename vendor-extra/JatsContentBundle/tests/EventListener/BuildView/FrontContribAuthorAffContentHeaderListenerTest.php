<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\JatsContentBundle\EventListener\BuildView\FrontContribAuthorAffContentHeaderListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\CallbackViewConverter;
use Libero\ViewsBundle\Views\EmptyView;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;
use function is_string;

final class FrontContribAuthorAffContentHeaderListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $listener = new FrontContribAuthorAffContentHeaderListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/content-header.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
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
    public function it_does_nothing_if_is_not_the_content_header_template() : void
    {
        $listener = new FrontContribAuthorAffContentHeaderListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <contrib-group>
            <contrib contrib-type="author">
                <aff>foo</aff>
            </contrib>
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
    public function it_does_nothing_if_there_is_no_aff() : void
    {
        $listener = new FrontContribAuthorAffContentHeaderListener($this->createFailingConverter());

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
    public function it_does_nothing_if_there_is_already_affiliations_set() : void
    {
        $listener = new FrontContribAuthorAffContentHeaderListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <contrib-group>
            <contrib contrib-type="author">
                <aff>foo</aff>
            </contrib>
        </contrib-group>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/content-header.html.twig', ['affiliations' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertSame(['affiliations' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_affiliations_argument() : void
    {
        $listener = new FrontContribAuthorAffContentHeaderListener(
            new CallbackViewConverter(
                static function (NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : View {
                    if (!$node instanceof Element
                        || !is_string($template)
                        || '{http://jats.nlm.nih.gov}aff' !== $node->clarkNotation()) {
                        return new EmptyView();
                    }

                    return new TemplateView($template, ['text' => (string) $node], $context);
                }
            )
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-meta>
        <jats:contrib-group>
            <jats:contrib contrib-type="author">
                <jats:aff>Affiliation 1</jats:aff>
            </jats:contrib>
            <jats:contrib contrib-type="author">
                <jats:aff>Affiliation 2</jats:aff>
                <jats:aff>Affiliation 1</jats:aff>
            </jats:contrib>
            <jats:contrib contrib-type="not-author">
                <jats:aff>Affiliation 1</jats:aff>
            </jats:contrib>
        </jats:contrib-group>
        <jats:contrib-group>
            <jats:contrib contrib-type="author">
                <jats:aff>Affiliation 1</jats:aff>
                <jats:aff>Affiliation 3</jats:aff>
            </jats:contrib>
        </jats:contrib-group>
    </jats:article-meta>
</jats:front>
XML
        );

        $context = ['bar' => 'baz'];

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/content-header.html.twig', [], $context)
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'affiliations' => [
                    'items' => [
                        [
                            'content' => [
                                'text' => 'Affiliation 1',
                            ],
                        ],
                        [
                            'content' => [
                                'text' => 'Affiliation 2',
                            ],
                        ],
                        [
                            'content' => [
                                'text' => 'Affiliation 3',
                            ],
                        ],
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['bar' => 'baz'], $view->getContext());
    }
}