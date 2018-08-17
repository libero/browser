<?php

namespace tests\Libero\ContentPageBundle\Controller;

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
        $controller = new ContentController();

        $response = $controller($id);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
        $this->assertSame($id, $response->getContent());
    }

    public function idProvider() : iterable
    {
        yield 'ID foo' => ['foo'];
        yield 'ID bar' => ['bar'];
    }
}
