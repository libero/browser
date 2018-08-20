<?php

namespace Libero\ApiClientBundle;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

interface ApiClientInterface
{
    public function send(RequestInterface $request) : PromiseInterface;
}
