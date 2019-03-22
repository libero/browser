<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle\EventListener;

use Libero\LiberoPageBundle\Event\CreatePageEvent;
use Libero\LiberoPageBundle\EventListener\TitleListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use tests\Libero\LiberoPageBundle\PageTestCase;
use tests\Libero\LiberoPageBundle\XmlTestCase;

final class TitleListenerTest extends TestCase
{
    use PageTestCase;
    use XmlTestCase;

    /**
     * @test
     */
    public function it_sets_the_title() : void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['libero.page.site_name' => 'Site Name'], 'en');

        $mainListener = new TitleListener($translator);

        $event = new CreatePageEvent($this->createRequest('page'));

        $mainListener->onCreatePage($event);

        $this->assertSame('Site Name', $event->getTitle());
    }

    /**
     * @test
     */
    public function it_updates_the_title() : void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            [
                'libero.page.site_name' => 'Site Name',
                'libero.page.page_title' => "page_title: '{page_title}'; site_name: '{site_name}'",
            ],
            'en'
        );

        $mainListener = new TitleListener($translator);

        $event = new CreatePageEvent($this->createRequest('page'));
        $event->setTitle('Page Title');

        $mainListener->onCreatePage($event);

        $this->assertSame("page_title: 'Page Title'; site_name: 'Site Name'", $event->getTitle());
    }
}
