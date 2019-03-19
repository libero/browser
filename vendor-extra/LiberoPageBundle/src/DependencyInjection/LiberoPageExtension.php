<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\DependencyInjection;

use Libero\LiberoPageBundle\Controller\PageController;
use Libero\LiberoPageBundle\EventListener\LiberoPageListener;
use Libero\LiberoPageBundle\Routing\PageRouteLoader;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use function array_column;
use function array_keys;

final class LiberoPageExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->setAlias('libero.client', $config['client']);
        $container->setParameter('libero.page_template', $config['page_template']);

        foreach (array_keys($config['pages']) as $name) {
            $config['pages'][$name]['name'] = $name;
            $config['pages'][$name]['controller'] = PageController::class;
            $config['pages'][$name]['route'] = "libero.page.{$config['pages'][$name]['type']}.{$name}";
        }

        $container->findDefinition(PageRouteLoader::class)
            ->setArgument(0, $config['pages']);

        $container->findDefinition(LiberoPageListener::class)
            ->setArgument(0, array_column($config['pages'], null, 'route'));
    }

    public function getConfiguration(array $config, ContainerBuilder $container) : ConfigurationInterface
    {
        return new LiberoPageConfiguration($this->getAlias());
    }

    public function getAlias() : string
    {
        return 'libero_page';
    }
}
