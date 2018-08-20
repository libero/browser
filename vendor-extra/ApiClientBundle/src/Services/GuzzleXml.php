<?php
namespace Libero\ApiClient\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class GuzzleXml
{
    public function __constructor()
    {
        $this->client = new Client();
    }

    public function send(RequestInterface $request) : PromiseInterface
    {
        return $this->client
            ->sendAsync($request)
            ->then(
                function (ResponseInterface $e) : XmlResponse {
                    return new XmlResponse();
                }
            );
    }
}
