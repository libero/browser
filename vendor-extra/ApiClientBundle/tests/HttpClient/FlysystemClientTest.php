<?php

declare(strict_types=1);

namespace tests\Libero\ApiClientBundle\HttpClient;

use GuzzleHttp\Psr7\Request;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Libero\ApiClientBundle\HttpClient\FlysystemClient;
use PHPUnit\Framework\TestCase;
use function file_get_contents;

final class FlysystemClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_an_xml_file() : void
    {
        $flysystem = new Filesystem(new Local(__DIR__.'/../../src/Resources'));
        $client = new FlysystemClient($flysystem);

        $request = new Request('GET', '/foo');
        $response = $client->send($request)->wait();

        $this->assertSame(file_get_contents(__DIR__.'/../../src/Resources/front.xml'), $response);
    }
}
