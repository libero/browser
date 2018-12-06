<?php

declare(strict_types=1);

namespace tests\Libero\ContentPageBundle;

use PHPUnit\Framework\MockObject\MockObject;
use Twig\Environment;
use function GuzzleHttp\json_encode;

trait TwigTestCase
{
    /**
     * @return Environment&MockObject
     */
    public function createTwig() : Environment
    {
        $twig = $this->createMock(Environment::class);

        $twig->method('render')
            ->willReturnCallback(
                function (...$arguments) : string {
                    return json_encode($arguments);
                }
            );

        return $twig;
    }

    abstract protected function createMock(string $classname) : MockObject;
}
