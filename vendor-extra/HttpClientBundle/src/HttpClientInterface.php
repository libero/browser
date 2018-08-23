<?php
namespace Libero\HttpClientBundle;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

interface HttpClientInterface
{
    public function send() : PromiseInterface;
    public static function buildClient(array $config = []);
}
