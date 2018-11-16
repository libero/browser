<?php

declare(strict_types=1);

namespace Libero\ApiClientBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class ApiClientExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('clients.xml');

        $flysystem = $container->getDefinition('libero.api_client_bundle.flysystem.local');
        $flysystem->replaceArgument(0, __DIR__.'/../Resources');
    }
}
