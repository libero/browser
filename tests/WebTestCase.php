<?php

declare(strict_types=1);

namespace tests\Libero\Browser;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    use AppKernelTestCase;
}
