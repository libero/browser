<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\LiberoPageBundle\EventListener\HomepageContentHeaderListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\UrlGeneratorTestCase;

final class HomepageContentHeaderListenerTest extends TestCase
{
    use PageTestCase;
    use UrlGeneratorTestCase;

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_homepage() : void
    {
        $homepageContentHeaderListener = new HomepageContentHeaderListener([], new IdentityTranslator());

        $event = new CreatePagePartEvent('template', $this->createRequest('not-homepage'));

        $homepageContentHeaderListener->onCreatePagePart($event);

        $this->assertEmpty($event->getContent());
    }

    /**
     * @test
     */
    public function it_sets_the_content_title_to_the_site_name() : void
    {
        $translator = new Translator('es');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['libero.page.site_name' => 'Site Name'], 'es');

        $homepageContentHeaderListener = new HomepageContentHeaderListener([], $translator);

        $event = new CreatePagePartEvent('template', $this->createRequest('homepage'), [], ['lang' => 'es']);

        $homepageContentHeaderListener->onCreatePagePart($event);

        $this->assertSame(['text' => 'Site Name'], $event->getContent()[0]['content'][0]['arguments']['contentTitle']);
    }

    /**
     * @test
     */
    public function it_does_not_set_the_image_if_there_is_no_src() : void
    {
        $homepageContentHeaderListener = new HomepageContentHeaderListener(['foo' => 'bar'], new IdentityTranslator());

        $event = new CreatePagePartEvent('template', $this->createRequest('homepage'));

        $homepageContentHeaderListener->onCreatePagePart($event);

        $this->assertArrayNotHasKey('image', $event->getContent()[0]['content'][0]['arguments']);
    }

    /**
     * @test
     * @dataProvider imageProvider
     */
    public function it_sets_the_image(array $image, array $expected) : void
    {
        $homepageContentHeaderListener = new HomepageContentHeaderListener($image, new IdentityTranslator());

        $event = new CreatePagePartEvent('template', $this->createRequest('homepage'));

        $homepageContentHeaderListener->onCreatePagePart($event);

        $this->assertSame($expected, $event->getContent()[0]['content'][0]['arguments']['image']);
    }

    public function imageProvider() : iterable
    {
        yield 'no sources' => [
            ['src' => 'http://example.com/src'],
            ['image' => ['src' => 'http://example.com/src']],
        ];

        yield 'empty sources' => [
            [
                'src' => 'http://example.com/src',
                'sources' => [],
            ],
            ['image' => ['src' => 'http://example.com/src']],
        ];

        yield 'sources' => [
            [
                'src' => 'http://example.com/src1',
                'sources' => [
                    ['srcset' => 'http://example.com/src2'],
                ],
            ],
            [
                'image' => ['src' => 'http://example.com/src1'],
                'sources' => [
                    ['srcset' => 'http://example.com/src2'],
                ],
            ],
        ];
    }
}
