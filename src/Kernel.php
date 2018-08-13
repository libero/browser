<?php

namespace Libero\Browser;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir()
    {
        return "{$this->getProjectDir()}/var/cache/{$this->environment}";
    }

    public function getLogDir()
    {
        return "{$this->getProjectDir()}/var/log";
    }

    public function registerBundles()
    {
        $contents = require "{$this->getConfigDir()}/bundles.php";
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->addResource(new FileResource("{$this->getConfigDir()}/bundles.php"));
        $container->setParameter('container.dumper.inline_class_loader', true);

        $loader->load("{$this->getConfigDir()}/{packages}/*.yaml", 'glob');
        $loader->load("{$this->getConfigDir()}/{packages}/{$this->environment}/**/*.yaml", 'glob');
        $loader->load("{$this->getConfigDir()}/{services}.yaml", 'glob');
        $loader->load("{$this->getConfigDir()}/{services}_{$this->environment}.yaml", 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import("{$this->getConfigDir()}/{routes}/*.yaml", '/', 'glob');
        $routes->import("{$this->getConfigDir()}/{routes}/{$this->environment}/**/*.yaml", '/', 'glob');
        $routes->import("{$this->getConfigDir()}/{routes}.yaml", '/', 'glob');
    }

    private function getConfigDir() : string
    {
        return $this->getProjectDir().'/config';
    }
}
