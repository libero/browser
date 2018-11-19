<?php

declare(strict_types=1);

namespace tests\Libero\Browser;

use Csa\GuzzleHttp\Middleware\Cache\Adapter\MockStorageAdapter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

trait AppKernelTestCase
{
    /** @var ContainerInterface */
    protected static $container;

    final protected static function bootKernel(array $options = []) : KernelInterface
    {
        $kernel = parent::bootKernel($options);

        (new Filesystem())->remove(static::$container->getParameter('guzzle_mocks'));

        return $kernel;
    }

    final protected static function createKernel(array $options = []) : KernelInterface
    {
        $kernel = parent::createKernel($options);

        if (!$kernel->isDebug()) {
            (new Filesystem())->remove($kernel->getCacheDir());
        }

        return $kernel;
    }

    final protected static function mockApiResponse(RequestInterface $request, ResponseInterface $response) : void
    {
        /** @var MockStorageAdapter $mock */
        $mock = static::$container->get('csa_guzzle.mock.storage');

        $mock->save($request, $response);
    }
}
