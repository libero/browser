<?php

namespace tests\Libero\Browser;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WebTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_returns_a_404_when_previewing_a_404_page() : void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
