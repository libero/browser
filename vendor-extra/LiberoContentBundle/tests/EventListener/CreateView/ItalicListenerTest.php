<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\EventListener\CreateView;

use Libero\LiberoContentBundle\EventListener\CreateView\ItalicListener;
use Libero\ViewsBundle\Event\CreateViewEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class ItalicListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_italic_element(string $xml) : void
    {
        $listener = new ItalicListener($this->createFailingConverter());

        $element = $this->loadElement($xml);

        $event = new CreateViewEvent($element, new View(null));
        $listener->onCreateView($event);
        $view = $event->getView();

        $this->assertNull($view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<italic xmlns="http://example.com">foo</italic>'];
        yield 'different element' => ['<bold xmlns="http://libero.pub">foo</bold>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_italic_template() : void
    {
        $listener = new ItalicListener($this->createFailingConverter());

        $element = $this->loadElement('<i xmlns="http://libero.pub">foo</i>');

        $event = new CreateViewEvent($element, new View('template'));
        $listener->onCreateView($event);
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
        $listener = new ItalicListener($this->createFailingConverter());

        $element = $this->loadElement('<italic xmlns="http://libero.pub">foo</italic>');

        $event = new CreateViewEvent($element, new View(null, ['text' => 'bar']));
        $listener->onCreateView($event);
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
        $listener = new ItalicListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<libero:italic xmlns:libero="http://libero.pub">
    foo <libero:bold>bar</libero:bold> baz
</libero:italic>
XML
        );

        $context = ['qux' => 'quux'];

        $event = new CreateViewEvent($element, new View(null, [], $context));
        $listener->onCreateView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/italic.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View(
                        null,
                        ['node' => '/libero:italic/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/libero:italic/libero:bold', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/libero:italic/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
