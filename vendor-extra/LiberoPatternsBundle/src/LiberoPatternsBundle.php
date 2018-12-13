<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle;

use Libero\LiberoPatternsBundle\DependencyInjection\LiberoPatternsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LiberoPatternsBundle extends Bundle
{
    protected function createContainerExtension() : ExtensionInterface
    {
        return new LiberoPatternsExtension();
    }
}
