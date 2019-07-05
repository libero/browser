<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DebugEventDispatcherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        $container->removeDefinition('debug.event_dispatcher');
    }
}
