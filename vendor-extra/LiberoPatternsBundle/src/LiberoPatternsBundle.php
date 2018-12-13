<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle;

use Libero\LiberoPatternsBundle\DependencyInjection\Compiler\AddInlineViewConvertersPass;
use Libero\LiberoPatternsBundle\DependencyInjection\Compiler\AddViewConvertersPass;
use Libero\LiberoPatternsBundle\DependencyInjection\LiberoPatternsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LiberoPatternsBundle extends Bundle
{
    public function build(ContainerBuilder $container) : void
    {
        $container->addCompilerPass(new AddInlineViewConvertersPass());
        $container->addCompilerPass(new AddViewConvertersPass());
    }

    protected function createContainerExtension() : ExtensionInterface
    {
        return new LiberoPatternsExtension();
    }
}
