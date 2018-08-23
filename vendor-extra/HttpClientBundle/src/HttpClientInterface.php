<?php
namespace Libero\HttpClientBundle\Http;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

interface HttpClientInterface
{
    public function send(RequestInterface $request) : PromiseInterface;
    public static function buildClient(array $config = []) : void;
}
