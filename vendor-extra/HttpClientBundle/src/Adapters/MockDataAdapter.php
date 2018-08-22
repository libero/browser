<?php
namespace Libero\ApiClientBundle\Services;

use League\Flysystem\Filesystem;

final class MockDataAdapter implements HttpClientInterface
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
        $promise = $this->client->read(__DIR__.'/../Resources/front.xml');
        return new Promise($promise, $request);
    }

    private static function buildClient(array $config = [])
    {

        $adapter = new Local(__DIR__ . '/uploads');
        return new Filesystem($adapter);
    }
}
