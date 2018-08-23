<?php
namespace Libero\HttpClientBundle\Clients;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use GuzzleHttp\Promise\PromiseInterface;
use Libero\HttpClientBundle\HttpClientInterface;

final class FlysystemClient implements HttpClientInterface
{
    private $client;

    public function __construct(Filesystem $client = null)
    {
        if (!$client) {
            $client = static::buildClient();
        }
        $this->client = $client;
    }

    public function send() : PromiseInterface
    {
        $result = $this->client->read('front.xml');
        return \GuzzleHttp\Promise\promise_for($result);
    }

    public static function buildClient(array $config = [])
    {
        $adapter = new Local(__DIR__.'/../Resources/');
        return new Filesystem($adapter);
    }
}
