<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\Controller;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Libero\ContentPageBundle\Controller\ContentController;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\GuzzleTestCase;
use UnexpectedValueException;

final class ContentControllerTest extends TestCase
{
    use GuzzleTestCase;

    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_returns_the_title(string $id) : void
    {
        $controller = new ContentController($this->client, 'service');

        $this->mock->save(
            new Request(
                'GET',
                "service/items/{$id}/versions/latest",
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
        <title>Article {$id}</title>
    </front>
</item>
XML
            )
        );

        $response = $controller($id);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
        $this->assertSame("Article ${id}", $response->getContent());
    }

    public function idProvider() : iterable
    {
        yield 'ID foo' => ['foo'];
        yield 'ID bar' => ['bar'];
    }

    /**
     * @test
     */
    public function it_throws_http_errors() : void
    {
        $controller = new ContentController($this->client, 'service');

        $this->mock->save(
            new Request(
                'GET',
                'service/items/id/versions/latest',
                ['Accept' => 'application/xml']
            ),
            new Response(
                404,
                [],
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<problem xmlns="urn:ietf:rfc:7807" xml:lang="en">
    <status>404</status>
    <title>Not Found</title>
</problem>
XML
            )
        );

        $this->expectException(ClientException::class);
        $this->expectExceptionMessageRegExp('/404 Not Found/');

        $controller('id');
    }

    /**
     * @test
     */
    public function it_fails_if_it_does_not_find_the_title() : void
    {
        $controller = new ContentController($this->client, 'service');

        $this->mock->save(
            new Request(
                'GET',
                'service/items/id/versions/latest',
                ['Accept' => 'application/xml']
            ),
            new Response(
                200,
                [],
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo/>
XML
            )
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not find a title');

        $controller('id');
    }
}
