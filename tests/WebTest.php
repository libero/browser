<?php

namespace tests\Libero\Browser;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class WebTest extends BaseWebTestCase
{
    /**
     * @test
     */
    public function it_returns_a_404_when_previewing_a_404_page() : void
    {
        $client = static::createClient();

        $this->expectException(NotFoundHttpException::class);

        $client->request('GET', '/');
    }
}
