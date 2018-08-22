<?php
namespace Libero\HttpClientBundle\Adapter;

use Libero\HttpClientBundle\Http\HttpClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

final class GuzzleAdapter implements HttpClientInterface
{
    private $client;

    public function __construct(ClientInterface $client = null)
    {
        if (!$client) {
            $client = static::buildClient();
        }
        $this->client = $client;
    }

    public function send(RequestInterface $request)
    {
        $promise = $this->client->sendAsync($request);
        return new Promise($promise, $request);
    }

    private static function buildClient(array $config = [])
    {
        $handlerStack = new HandlerStack(\GuzzleHttp\choose_handler());
        $config = array_merge(['handler' => $handlerStack], $config);
        return new GuzzleClient($config);
    }
}
