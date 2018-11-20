<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\DependencyInjection;

use Libero\ContentPageBundle\Controller\ContentController;
use Libero\ContentPageBundle\Routing\ContentPageRouteLoader;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use function sprintf;

final class ContentPageExtension extends Extension
{
    private const CONTENT_CONTROLLER_ID = 'libero.content_page.controller.content.%s';

    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        foreach ($config['pages'] as $name => $page) {
            $config['pages'][$name]['name'] = $name;
            $this->addPage($name, $page, $container);
        }

        $container->findDefinition(ContentPageRouteLoader::class)->setArgument(0, $config['pages']);
    }

    private function addPage(string $name, array $config, ContainerBuilder $container) : void
    {
        $id = sprintf(self::CONTENT_CONTROLLER_ID, $name);
        $definition = new Definition(ContentController::class);

        $definition->addTag('controller.service_arguments');

        $container->setDefinition($id, $definition);
    }

    public function getConfiguration(array $config, ContainerBuilder $container) : ConfigurationInterface
    {
        return new ContentPageConfiguration($this->getAlias());
    }

    public function getAlias() : string
    {
        return 'content_page';
    }
}
