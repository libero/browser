<?php
namespace Libero\ApiClientBundle\Services;

use League\Flysystem\Filesystem;

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

    public function send(RequestInterface $request)
    {
        $result = $this->client->read(__DIR__.'/../Resources/front.xml');
        return new Promise\promise_for($result);
    }

    private static function buildClient(array $config = [])
    {
        $adapter = new Local(__DIR__ . '/uploads');
        return new Filesystem($adapter);
    }
}
