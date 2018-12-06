<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\Controller;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Libero\ContentPageBundle\Controller\ContentController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use tests\Libero\ContentPageBundle\GuzzleTestCase;
use tests\Libero\ContentPageBundle\TwigTestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;
use UnexpectedValueException;
use function GuzzleHttp\json_encode;

final class ContentControllerTest extends TestCase
{
    use GuzzleTestCase;
    use TwigTestCase;
    use ViewConvertingTestCase;

    /**
     * @test
     * @dataProvider idProvider
     */
    public function it_returns_the_title(string $id) : void
    {
        $controller = $this->createContentController();

        $this->mock->save(
            new Psr7Request(
                'GET',
                "service/items/{$id}/versions/latest",
                ['Accept' => 'application/xml']
            ),
            new Psr7Response(
                200,
                [],
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<libero:item xmlns:libero="http://libero.pub">
    <libero:front xml:lang="en">
        <libero:id>{$id}</libero:id>
        <libero:title>Article {$id}</libero:title>
    </libero:front>
</libero:item>
XML
            )
        );

        $response = $controller(new Request(), $id);
        $response->prepare(new Request());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'template.html.twig',
                    [
                        'lang' => 'en',
                        'dir' => 'ltr',
                        'main' => [
                            [
                                'template' => '@LiberoPatterns/content-header.html.twig',
                                'arguments' => [
                                    'element' => '/libero:item/libero:front',
                                    'context' => [
                                        'lang' => 'en',
                                        'dir' => 'ltr',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ),
            $response->getContent()
        );
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
        $controller = $this->createContentController();

        $this->mock->save(
            new Psr7Request(
                'GET',
                'service/items/id/versions/latest',
                ['Accept' => 'application/xml']
            ),
            new Psr7Response(
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

        $controller(new Request(), 'id');
    }

    /**
     * @test
     */
    public function it_fails_if_it_does_not_find_the_front() : void
    {
        $controller = $this->createContentController();

        $this->mock->save(
            new Psr7Request(
                'GET',
                'service/items/id/versions/latest',
                ['Accept' => 'application/xml']
            ),
            new Psr7Response(
                200,
                [],
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo/>
XML
            )
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not find a front');

        $controller(new Request(), 'id');
    }

    private function createContentController(
        string $service = 'service',
        string $template = 'template.html.twig'
    ) : ContentController {
        return new ContentController(
            $this->client,
            $service,
            $this->createTwig(),
            $this->createConverter(),
            $template
        );
    }
}
