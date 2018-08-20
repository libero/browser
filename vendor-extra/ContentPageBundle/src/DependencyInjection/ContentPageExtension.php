<?php

namespace Libero\ContentPageBundle\DependencyInjection;

use Libero\ContentPageBundle\Controller\ContentController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class ContentPageExtension extends Extension
{
    private const CONTENT_CONTROLLER_ID = 'libero.content_page.controller.content.%s';

    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['pages'] as $name => $page) {
            $this->addPage($name, $page, $container);
        }
    }

    private function addPage(string $name, array $config, ContainerBuilder $container) : void
    {
        $id = sprintf(self::CONTENT_CONTROLLER_ID, $name);
        $definition = new Definition(ContentController::class);
       
        $definition->setAutowired(true);
        $definition->addTag('controller.service_arguments');
        $container->setDefinition($id, $definition);
    }
}
