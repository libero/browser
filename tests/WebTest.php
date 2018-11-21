<?php

declare(strict_types=1);

namespace tests\Libero\Browser;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WebTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_shows_scholarly_articles(string $id) : void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', "/articles/{$id}");
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame($id, $crawler->text());
    }

    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_shows_blog_articles(string $id) : void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', "/blog/{$id}");
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame($id, $crawler->text());
    }

    public function idProvider() : iterable
    {
        yield 'ID foo' => ['foo'];
        yield 'ID bar' => ['bar'];
    }
}
