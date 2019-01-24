<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use function array_keys;
use function is_string;

final class ContentHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        $controllers = $container->findTaggedServiceIds('libero.content_page.controller');
        $handlers = $container->findTaggedServiceIds('libero.content_page.handler');

        foreach (array_keys($controllers) as $controller) {
            $this->addHandler($container->findDefinition($controller), $handlers);
        }
    }

    private function addHandler(Definition $controller, array $handlers) : void
    {
        $alias = $controller->getArgument(4);

        if (!is_string($alias)) {
            return;
        }

        foreach ($handlers as $handler => $tags) {
            if ($tags[0]['alias'] === $alias) {
                $controller->setArgument(4, new Reference($handler));

                return;
            }
        }

        throw new LogicException("Could not find handler '{$alias}'");
    }
}
