<?php
namespace Libero\ApiClientBundle\Services;

use Libero\ApiClientBundle\ApiClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Article implements ApiClientInterface
{
    public function __construct(ReadData $client)
    {
        $this->client =  $client;
    }

    public function getData(string $data = 'body')
    {
        return $this->client->send($data);
    }
}
