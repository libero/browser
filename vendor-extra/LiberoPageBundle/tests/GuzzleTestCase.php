<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use Csa\GuzzleHttp\Middleware\Cache\Adapter\MockStorageAdapter;
use Csa\GuzzleHttp\Middleware\Cache\Adapter\StorageAdapterInterface;
use Csa\GuzzleHttp\Middleware\Cache\MockMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use VirtualFileSystem\FileSystem;

trait GuzzleTestCase
{
    /** @var ClientInterface */
    protected $client;
    /** @var FileSystem */
    protected $filesystem;
    /** @var StorageAdapterInterface */
    protected $mock;

    /**
     * @before
     */
    final public function setUpGuzzle() : void
    {
        $this->filesystem = new FileSystem();

        $stack = HandlerStack::create(new MockHandler());
        $stack->push(
            new MockMiddleware(
                $this->mock = new MockStorageAdapter(
                    $this->filesystem->root()->url(),
                    ['User-Agent']
                ),
                'replay'
            )
        );

        $this->client = new Client(['handler' => $stack]);
    }
}
