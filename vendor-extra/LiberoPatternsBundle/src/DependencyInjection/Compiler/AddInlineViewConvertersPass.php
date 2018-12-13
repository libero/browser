<?php

declare(strict_types=1);

namespace Libero\LiberoPatternsBundle\DependencyInjection\Compiler;

use Libero\Views\InlineViewConverterRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function array_keys;
use function array_map;

final class AddInlineViewConvertersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        $registry = $container->findDefinition(InlineViewConverterRegistry::class);

        $viewConverters = array_keys($container->findTaggedServiceIds('libero.view_converter.inline'));

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
