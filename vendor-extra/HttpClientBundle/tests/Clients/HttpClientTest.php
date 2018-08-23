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
    public function it_returns_a_xml_file() : void
    {
        $client = new FlysystemClient();
        
    }

}
