<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePagePartEvent;
use Libero\LiberoPageBundle\EventListener\SiteHeaderListener;
use PHPUnit\Framework\TestCase;
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

        $mainListener = new SiteHeaderListener($translator, $this->createDumpingUrlGenerator());

        $event = new CreatePagePartEvent('template', $this->createRequest('page'));

        $mainListener->onCreatePagePart($event);

        $this->assertSame(
            $translator->trans('libero.page.site_name'),
            $translator->trans(
                $event->getContent()[0]['content'][0]['arguments']['logo']['image']['alt']
            )
        );
    }
}
