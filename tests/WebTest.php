<?php

namespace tests\Libero\Browser;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WebTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_shows_a_content_page(string $id) : void
    {
        $client = static::createClient();

        $client->request('GET', "/content/{$id}");
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame($id, $response->getContent());
    }

    public function idProvider() : iterable
    {
        yield 'ID foo' => ['foo'];
        yield 'ID bar' => ['bar'];
    }
}
