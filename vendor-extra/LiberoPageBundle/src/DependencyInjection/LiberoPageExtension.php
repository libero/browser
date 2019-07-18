<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\DependencyInjection;

use Libero\LiberoPageBundle\Controller\PageController;
use Libero\LiberoPageBundle\EventListener\HomepageContentHeaderListener;
use Libero\LiberoPageBundle\EventListener\InfoBarListener;
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

        $config['pages']['homepage'] = ['homepage' => $config['pages']['homepage']];

        $pages = [];
        foreach (array_keys($config['pages'] ?? []) as $type) {
            foreach (array_keys($config['pages'][$type]) as $name) {
                $page = $config['pages'][$type][$name];
                $page['name'] = $name;
                $page['type'] = $type;
                $page['controller'] = PageController::class;
                $page['route'] = $name === $type ? "libero.page.{$type}" : "libero.page.{$type}.{$name}";
                $pages[] = $page;
            }
        }

        $container->findDefinition(PageRouteLoader::class)
            ->setArgument(0, $pages);

        $container->findDefinition(LiberoPageListener::class)
            ->setArgument(0, array_column($pages, null, 'route'));

        $container->findDefinition(InfoBarListener::class)
            ->setArgument(0, $config['info_bar']['text'] ?? null);

        $container->findDefinition(HomepageContentHeaderListener::class)
            ->setArgument(0, $config['pages']['homepage']['homepage']['header_image'] ?? []);
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
