<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use Symfony\Component\HttpFoundation\Request;

trait PageTestCase
{
    final protected function createRequest(string $type) : Request
    {
        $request = new Request();
        $request->attributes->set('libero_page', ['type' => $type]);

        return $request;
    }
}
