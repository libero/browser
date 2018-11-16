<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\Controller;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Libero\ApiClientBundle\HttpClient\FlysystemClient;
use Libero\ContentPageBundle\Controller\ContentController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContentControllerTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_returns_a_id(string $id) : void
    {
        $flysystem = new Filesystem(new Local(__DIR__.'/../../src/Resources'));
        $controller = new ContentController(new FlysystemClient($flysystem));

        $response = $controller($id);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
        $this->assertSame('Coordination in Centralized and Decentralized Systems', $response->getContent());
    }

    public function idProvider() : iterable
    {
        yield 'ID foo' => ['foo'];
        yield 'ID bar' => ['bar'];
    }
}
