<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function json_encode;

trait UrlGeneratorTestCase
{
    final protected function createDumpingUrlGenerator() : UrlGeneratorInterface
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $urlGenerator->method('generate')->willReturnCallback(
            static function (string $name, array $parameters) : string {
                return "{$name}/".json_encode($parameters);
            }
        );

        return $urlGenerator;
    }

    final protected function createFailingUrlGenerator() : UrlGeneratorInterface
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $urlGenerator->expects($this->never())->method($this->anything());

        return $urlGenerator;
    }
}
