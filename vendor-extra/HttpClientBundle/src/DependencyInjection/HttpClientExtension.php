<?php

namespace Libero\HttpClientBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Libero\HttpClientBundle\Clients\FlysystemClient;
use Libero\HttpClientBundle\HttpClientInterface;

final class HttpClientExtension extends Extension
{

    private const HTTP_CLIENT_ID = 'libero.http_client';

    public function load(array $configs, ContainerBuilder $container)
    {

        $definition = new Definition(FlysystemClient::class);
        $definition->setAutowired(true)->setAutoconfigured(true)->setPublic(false);
        
        $container->setDefinition(self::HTTP_CLIENT_ID, $definition);
        $container->setAlias(HttpClientInterface::class, self::HTTP_CLIENT_ID);
    }
}
