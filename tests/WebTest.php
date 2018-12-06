<?php

declare(strict_types=1);

namespace tests\Libero\Browser;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class WebTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_shows_scholarly_articles(string $id) : void
    {
        $client = static::createClient();

        self::mockApiResponse(
            new Request(
                'GET',
                "http://localhost/scholarly-articles/items/{$id}/versions/latest",
                ['Accept' => 'application/xml']
            ),
            new Response(
                200,
                [],
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <front xml:lang="en">
        <id>{$id}</id>
        <title>Scholarly article {$id}</title>
    </front>
</item>
XML
            )
        );

        $crawler = $client->request('GET', "/articles/{$id}");
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame("Scholarly article {$id}", $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_shows_blog_articles(string $id) : void
    {
        $client = static::createClient();

        self::mockApiResponse(
            new Request(
                'GET',
                "http://localhost/blog-articles/items/{$id}/versions/latest",
                ['Accept' => 'application/xml']
            ),
            new Response(
                200,
                [],
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <front xml:lang="en">
        <id>${id}</id>
        <title>Blog article ${id}</title>
    </front>
</item>
XML
            )
        );

        $crawler = $client->request('GET', "/blog/{$id}");
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame("Blog article {$id}", $crawler->filter('.content-header__title')->text());
    }

    public function idProvider() : iterable
    {
        yield 'ID foo' => ['foo'];
        yield 'ID bar' => ['bar'];
    }
}
