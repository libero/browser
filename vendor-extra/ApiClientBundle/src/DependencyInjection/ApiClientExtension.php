<?php

namespace Libero\ApiClientBundle\DependencyInjection;

use Libero\ApiClientBundle\ApiClientInterface;
use \Libero\ApiClientBundle\Services\Article;
use \Libero\ApiClientBundle\Services\ReadData;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;

final class ApiClientExtension extends Extension
{
    private const API_CLIENT_ARTICLE_ID = 'libero.api_client.article';

    public function load(array $configs, ContainerBuilder $container)
    {
        $args_definition = new Definition(ReadData::class);
        $args_definition->setAutowired(true)->setAutoconfigured(true)->setPublic(false);

        $definition = new Definition(Article::class);
        $definition->setAutowired(true)->setAutoconfigured(true)->setPublic(false)->setArgument('$client', $args_definition);

        $container->setDefinition(self::API_CLIENT_ARTICLE_ID, $definition);
        $container->setAlias(ApiClientInterface::class, self::API_CLIENT_ARTICLE_ID);
    }
}
