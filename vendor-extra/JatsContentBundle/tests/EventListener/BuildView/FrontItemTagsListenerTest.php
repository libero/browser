<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use FluentDOM\DOM\Node\NonDocumentTypeChildNode;
use Libero\JatsContentBundle\EventListener\BuildView\FrontItemTagsListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use PHPUnit\Framework\TestCase;
use tests\Libero\LiberoPageBundle\ViewConvertingTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class FrontItemTagsListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $listener = new FrontItemTagsListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/item-tags.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/item-tags.html.twig', $view->getTemplate());
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
    public function it_does_nothing_if_is_not_the_item_tags_template() : void
    {
        $listener = new FrontItemTagsListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <kwd-group kwd-group-type="foo">
            <kwd>foo</kwd>
        </kwd-group>
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
    public function it_does_nothing_if_there_is_no_kwd_group() : void
    {
        $listener = new FrontItemTagsListener($this->createFailingConverter());

        $element = $this->loadElement('<front xmlns="http://jats.nlm.nih.gov"><article-meta/></front>');

        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/item-tags.html.twig'));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/item-tags.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_groups_set() : void
    {
        $listener = new FrontItemTagsListener($this->createFailingConverter());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov">
    <article-meta>
        <kwd-group kwd-group-type="foo">
            <kwd>foo</kwd>
        </kwd-group>
    </article-meta>
</front>
XML
        );

        $event = new BuildViewEvent(
            $element,
            new TemplateView('@LiberoPatterns/item-tags.html.twig', ['groups' => 'bar'])
        );
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/item-tags.html.twig', $view->getTemplate());
        $this->assertSame(['groups' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_groups_argument_with_converted_kwd_groups() : void
    {
        $listener = new FrontItemTagsListener(
            $this->createFilteringConverter(
                $this->createDumpingConverter(),
                function (NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : bool {
                    return $node instanceof Element && $node->hasAttribute('kwd-group-type');
                }
            )
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-meta>
        <jats:kwd-group kwd-group-type="foo">
            <jats:kwd>foo</jats:kwd>
        </jats:kwd-group>
        <jats:kwd-group>
            <jats:kwd>bar</jats:kwd>
        </jats:kwd-group>
        <jats:kwd-group kwd-group-type="baz">
            <jats:kwd>baz</jats:kwd>
        </jats:kwd-group>
    </jats:article-meta>
</jats:front>
XML
        );

        $context = ['qux' => 'quux'];
        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/item-tags.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/item-tags.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'groups' => [
                    [
                        'node' => '/jats:front/jats:article-meta/jats:kwd-group[1]',
                        'template' => '@LiberoPatterns/tag-list.html.twig',
                        'context' => ['qux' => 'quux'],
                    ],
                    [
                        'node' => '/jats:front/jats:article-meta/jats:kwd-group[3]',
                        'template' => '@LiberoPatterns/tag-list.html.twig',
                        'context' => ['qux' => 'quux'],
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }

    /**
     * @test
     */
    public function it_does_not_set_the_groups_argument_if_no_kwd_groups_convert() : void
    {
        $listener = new FrontItemTagsListener(
            $this->createFilteringConverter(
                $this->createDumpingConverter(),
                function (NonDocumentTypeChildNode $node, ?string $template = null, array $context = []) : bool {
                    return false;
                }
            )
        );

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov">
    <jats:article-meta>
        <jats:kwd-group kwd-group-type="foo">
            <jats:kwd>foo</jats:kwd>
        </jats:kwd-group>
        <jats:kwd-group>
            <jats:kwd>bar</jats:kwd>
        </jats:kwd-group>
        <jats:kwd-group kwd-group-type="baz">
            <jats:kwd>baz</jats:kwd>
        </jats:kwd-group>
    </jats:article-meta>
</jats:front>
XML
        );

        $context = ['qux' => 'quux'];
        $event = new BuildViewEvent($element, new TemplateView('@LiberoPatterns/item-tags.html.twig', [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertInstanceOf(TemplateView::class, $view);
        $this->assertSame('@LiberoPatterns/item-tags.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
