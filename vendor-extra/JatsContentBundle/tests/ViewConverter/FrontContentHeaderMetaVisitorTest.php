<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use Libero\JatsContentBundle\ViewConverter\FrontContentHeaderMetaVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use tests\Libero\ContentPageBundle\XmlTestCase;

final class FrontContentHeaderMetaVisitorTest extends TestCase
{
    use ViewConvertingTestCase;
    use XmlTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_front_element(string $xml) : void
    {
        $visitor = new FrontContentHeaderMetaVisitor($this->createDumpingConverter(), new IdentityTranslator());

        $element = $this->loadElement($xml);

        $view = $visitor->visit($element, new View('@LiberoPatterns/content-header.html.twig'));

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
        $visitor = new FrontContentHeaderMetaVisitor($this->createDumpingConverter(), new IdentityTranslator());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov"/>
XML
        );

        $view = $visitor->visit($element, new View('template'));

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($view->getContext());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_meta_set() : void
    {
        $visitor = new FrontContentHeaderMetaVisitor($this->createDumpingConverter(), new IdentityTranslator());

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<front xmlns="http://jats.nlm.nih.gov"/>
XML
        );

        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/content-header.html.twig', ['meta' => ['foo']])
        );

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertSame(['meta' => ['foo']], $view->getArguments());
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
            ['libero.patterns.content_header.meta.label' => 'meta label in es'],
            'es',
            'messages'
        );

        $visitor = new FrontContentHeaderMetaVisitor($this->createDumpingConverter(), $translator);

        $element = $this->loadElement(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<jats:front xmlns:jats="http://jats.nlm.nih.gov"/>
XML
        );

        $context = ['lang' => 'es'];

        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/content-header.html.twig', [], $context)
        );

        $this->assertSame('@LiberoPatterns/content-header.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'meta' => [
                    'node' => '/jats:front',
                    'template' => '@LiberoPatterns/content-meta.html.twig',
                    'context' => ['lang' => 'es'],
                    'attributes' => [
                        'aria-label' => 'meta label in es',
                    ],
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['lang' => 'es'], $view->getContext());
    }
}
