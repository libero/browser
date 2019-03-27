<?php

declare(strict_types=1);

namespace tests\Libero\LiberoContentBundle\EventListener\CreateView;

use Libero\LiberoContentBundle\EventListener\CreateView\BoldListener;
use Libero\ViewsBundle\Event\CreateViewEvent;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class BoldListenerTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_libero_bold_element(string $xml) : void
    {
        $listener = new BoldListener($this->createFailingConverter());

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
        yield 'different namespace' => ['<bold xmlns="http://example.com">foo</bold>'];
        yield 'different element' => ['<italic xmlns="http://libero.pub">foo</italic>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_bold_template() : void
    {
        $listener = new BoldListener($this->createFailingConverter());

        $element = $this->loadElement('<bold xmlns="http://libero.pub">foo</bold>');

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
        $listener = new BoldListener($this->createFailingConverter());

        $element = $this->loadElement('<bold xmlns="http://libero.pub">foo</bold>');

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
        $listener = new BoldListener($this->createDumpingConverter());

        $element = $this->loadElement(
            <<<XML
<libero:bold xmlns:libero="http://libero.pub">
    foo <libero:italic>bar</libero:italic> baz
</libero:bold>
XML
        );

        $context = ['qux' => 'quux'];

        $event = new CreateViewEvent($element, new View(null, [], $context));
        $listener->onCreateView($event);
        $view = $event->getView();

        $this->assertSame('@LiberoPatterns/bold.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View(
                        null,
                        ['node' => '/libero:bold/text()[1]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/libero:bold/libero:italic', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                    new View(
                        null,
                        ['node' => '/libero:bold/text()[2]', 'template' => null, 'context' => ['qux' => 'quux']]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $view->getContext());
    }
}
