<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\LiberoPageBundle\EventListener\SiteHeaderListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\UrlGeneratorTestCase;

final class SiteHeaderListenerTest extends TestCase
{
    use PageTestCase;
    use UrlGeneratorTestCase;

  /**
   * @test
   */
    public function it_sets_the_default_logo_alt_text_to_the_site_name() : void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['libero.page.site_name' => 'Site Name'], 'en');

        $siteHeaderListener = new SiteHeaderListener($translator, $this->createDumpingUrlGenerator());

        $event = new CreatePagePartEvent('template', $this->createRequest('page'));

        $siteHeaderListener->onCreatePagePart($event);

        $this->assertSame(
            'Site Name',
            $event->getContent()[0]['content'][0]['arguments']['logo']['image']['alt']
        );
    }

  /**
   * @test
   */
    public function it_sets_the_href_to_the_homepage_path() : void
    {
        $siteHeaderListener = new SiteHeaderListener(new IdentityTranslator(), $this->createDumpingUrlGenerator());

        $event = new CreatePagePartEvent('template', $this->createRequest('page'));

        $siteHeaderListener->onCreatePagePart($event);

        $this->assertSame(
            'libero.page.homepage/{}',
            $event->getContent()[0]['content'][0]['arguments']['logo']['href']
        );
    }

    /**
     * @test
     */
    public function it_sets_the_menu() : void
    {
        $translator = new Translator('es');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['libero.page.menu.home' => 'home in es'], 'es');

        $siteHeaderListener = new SiteHeaderListener($translator, $this->createDumpingUrlGenerator());

        $event = new CreatePagePartEvent('template', $this->createRequest('page'), [], ['lang' => 'es']);

        $siteHeaderListener->onCreatePagePart($event);

        $this->assertSame(
            [
                [
                    'attributes' => [
                        'href' => 'libero.page.homepage/{}',
                    ],
                    'text' => 'home in es',
                ],
            ],
            $event->getContent()[0]['content'][0]['arguments']['menu']
        );
    }
}
