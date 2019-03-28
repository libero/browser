<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\EventListener\BuildView;

use Libero\JatsContentBundle\EventListener\BuildView\ParagraphListener;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class ParagraphListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_p_element(string $xml) : void
    {
        $listener = new ParagraphListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new BuildViewEvent($element, new View(null));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<p xmlns="http://example.com">foo</p>'];
        yield 'different element' => ['<sec xmlns="http://jats.nlm.nih.gov">foo</sec>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_paragraph_template() : void
    {
        $listener = new ParagraphListener($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

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
        $listener = new ParagraphListener($this->createFailingConverter());

        $element = $this->loadElement('<p xmlns="http://jats.nlm.nih.gov">foo</p>');

        $event = new BuildViewEvent($element, new View(null, ['text' => 'bar']));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertNull($view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_sets_the_template_and_text_argument() : void
    {
        $listener = new ParagraphListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<jats:p xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:bold>bar</jats:bold> baz
</jats:p>
XML
        );

        $context = ['qux' => 'quux'];

        $event = new BuildViewEvent($element, new View(null, [], $context));
        $listener->onBuildView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/paragraph.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View(
                        null,
                        ['node' => '/jats:p/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/jats:p/jats:bold', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/jats:p/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
