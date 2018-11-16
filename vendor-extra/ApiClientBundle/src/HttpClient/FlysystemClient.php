<?php

declare(strict_types=1);

namespace Libero\ApiClientBundle\HttpClient;

use GuzzleHttp\Promise\PromiseInterface;
use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\RequestInterface;
use function GuzzleHttp\Promise\promise_for;

final class FlysystemClient implements HttpClient
{
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function send(RequestInterface $request) : PromiseInterface
    {
        return promise_for($this->filesystem->read('front.xml'));
    }
}
