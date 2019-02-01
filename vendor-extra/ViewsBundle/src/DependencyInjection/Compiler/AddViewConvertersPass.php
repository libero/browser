<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\DependencyInjection\Compiler;

use Libero\ViewsBundle\Views\ViewConverterRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AddViewConvertersPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container) : void
    {
        $registry = $container->findDefinition(ViewConverterRegistry::class);

        $viewConverters = $this->findAndSortTaggedServices('libero.view_converter', $container);

        $registry->addMethodCall('add', $viewConverters);
    }
}
