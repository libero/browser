<?php
namespace Libero\HttpClientBundle\Clients;

use Libero\HttpClientBundle\HttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

final class GuzzleClient implements HttpClientInterface
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

    public static function buildClient(array $config = [])
    {
        $handlerStack = new HandlerStack(\GuzzleHttp\choose_handler());
        $config = array_merge(['handler' => $handlerStack], $config);
        return new Client($config);
    }
}
