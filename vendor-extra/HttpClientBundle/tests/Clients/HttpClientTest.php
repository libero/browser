<?php

namespace tests\Libero\HttpClientBundle\Clients;

use Libero\HttpClientBundle\Clients\FlysystemClient;
use Libero\HttpClientBundle\Clients\GuzzleClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HttpClientTest extends WebTestCase
{

    /**
     * @test
     */
    public function it_returns_a_promise() : void
    {
        $client = new FlysystemClient(); 
        $this->assertInstanceOf('GuzzleHttp\Promise\FulfilledPromise', $client->send());
    }

}
