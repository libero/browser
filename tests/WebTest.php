<?php

declare(strict_types=1);

namespace tests\Libero\Browser;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use function trim;

final class WebTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_has_a_homepage() : void
    {
        $client = self::createClient();

        self::mockApiResponse(
            new Request(
                'GET',
                'http://localhost/search',
                ['Accept' => 'application/xml']
            ),
            new Response(
                200,
                [],
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item-list xmlns="http://libero.pub">
    <item-ref id="article1" service="scholarly-articles"/>
</item-list>
XML
            )
        );

        self::mockApiResponse(
            new Request(
                'GET',
                'http://localhost/scholarly-articles/items/article1/versions/latest',
                ['Accept' => 'application/xml']
            ),
            new Response(
                200,
                [],
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<item xmlns="http://libero.pub">
    <meta>
        <id>article1</id>
        <service>scholarly-articles</service>
    </meta>
    <front xml:lang="en">
        <id>article1</id>
        <title>Scholarly article 1</title>
    </front>
</item>
XML
            )
        );

        $crawler = $client->request('GET', '/');
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('Site Name', trim($crawler->filter('.content-header__title')->text()));
        $this->assertSame('Scholarly article 1', trim($crawler->filter('.teaser__heading')->text()));
    }

    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_shows_scholarly_articles(string $id) : void
    {
        $client = self::createClient();

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
    <meta>
        <id>{$id}</id>
        <service>scholarly-articles</service>
    </meta>
    <front xml:lang="en">
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
        $this->assertSame("Scholarly article {$id}", trim($crawler->filter('.content-header__title')->text()));
    }

    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_shows_blog_articles(string $id) : void
    {
        $client = self::createClient();

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
    <meta>
        <id>{$id}</id>
        <service>scholarly-articles</service>
    </meta>
    <front xml:lang="en">
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
        $this->assertSame("Blog article {$id}", trim($crawler->filter('.content-header__title')->text()));
    }

    public function idProvider() : iterable
    {
        yield 'ID foo' => ['foo'];
        yield 'ID bar' => ['bar'];
    }
}
