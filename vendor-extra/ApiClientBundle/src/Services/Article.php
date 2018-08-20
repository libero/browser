<?php
namespace Libero\ApiClientBundle\Services;

use Libero\ApiClientBundle\ApiClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Article implements ApiClientInterface {

    public function __constructor () {
        $this->client =  new Client();
        $this->request = new Request('GET', 'http://httpbin.org');
    }

    public function send(RequestInterface $request) : PromiseInterface
    {
        return $this->client
            ->sendAsync($request)
            ->then(
                function (ResponseInterface $e) : XmlResponse {
                    return new XmlResponse($this->stopwatch, $e);
                }
            );
    }
}