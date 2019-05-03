<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle;

use Libero\LiberoPatternsBundle\DependencyInjection\Compiler\AssetPackagePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LiberoPatternsBundle extends Bundle
{
    public function build(ContainerBuilder $container) : void
    {
        $container->addCompilerPass(new AssetPackagePass());
    }
}
