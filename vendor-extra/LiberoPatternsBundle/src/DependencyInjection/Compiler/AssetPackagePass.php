<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

final class AssetPackagePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        try {
            $packages = $container->findDefinition('assets.packages');
        } catch (ServiceNotFoundException $e) {
            return;
        }

        $existingNamespacedPackages = $packages->getArgument(1);

        if (isset($existingNamespacedPackages['libero_patterns'])) {
            return;
        }

        $existingNamespacedPackages['libero_patterns'] = new Reference('libero.patterns.package');

        $packages->setArgument(1, $existingNamespacedPackages);
    }
}
