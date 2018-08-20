<?php

namespace Libero\ApiClientBundle\DependencyInjection;

use Libero\ApiClientBundle\ApiClientInterface;
use \Libero\ApiClientBundle\Services\Article;
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
        $definition = new Definition(Article::class);
        $definition->setAutowired(true)->setAutoconfigured(true)->setPublic(false);

        $container->setDefinition(self::API_CLIENT_ARTICLE_ID, $definition);
        $container->setAlias(ApiClientInterface::class, self::API_CLIENT_ARTICLE_ID);
    }
}
