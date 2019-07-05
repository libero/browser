<?php

declare(strict_types=1);

namespace Libero\ViewsBundle;

use Libero\ViewsBundle\DependencyInjection\Compiler\DebugEventDispatcherPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ViewsBundle extends Bundle
{
    public function build(ContainerBuilder $container) : void
    {
        $container->addCompilerPass(new DebugEventDispatcherPass());
    }
}
