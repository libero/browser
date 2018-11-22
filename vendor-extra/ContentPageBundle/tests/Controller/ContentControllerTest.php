<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle\Controller;

use Libero\ContentPageBundle\Controller\ContentController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

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
        $response->prepare(new Request());
        $crawler = new Crawler($response->getContent());

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
