<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\DependencyInjection\Compiler;

use Libero\Views\ViewConverterRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function array_keys;
use function array_map;

final class AddViewConvertersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        $registry = $container->findDefinition(ViewConverterRegistry::class);

        $viewConverters = array_keys($container->findTaggedServiceIds('libero.view_converter'));

        $registry->addMethodCall(
            'add',
            array_map(
                function (string $id) : Reference {
                    return new Reference($id);
                },
                $viewConverters
            )
        );
    }
}
