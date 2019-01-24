<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle;

use Libero\ContentPageBundle\DependencyInjection\Compiler\ContentHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ContentPageBundle extends Bundle
{
    public function build(ContainerBuilder $container) : void
    {
        $container->addCompilerPass(new ContentHandlerPass());
    }
}
