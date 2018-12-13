<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Environment;
use function GuzzleHttp\json_encode;

trait TwigTestCase
{
    /**
     * @return Environment&MockObject
     */
    final public function createTwig() : Environment
    {
        $twig = $this->createMock(Environment::class);

        $twig->method('render')
            ->willReturnCallback(
                function (...$arguments) : string {
                    return '<html><body>'.json_encode($arguments).'</body></html>';
                }
            );

        return $twig;
    }

    final protected function assertTwigRender(array $expected, string $actual) : void
    {
        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            (new Crawler($actual))->filter('body')->text()
        );
    }

    abstract protected function createMock(string $classname) : MockObject;

    abstract public static function assertJsonStringEqualsJsonString(
        string $expectedJson,
        string $actualJson,
        string $message = ''
    ) : void;
}
