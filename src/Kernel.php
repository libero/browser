<?php

declare(strict_types=1);

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

    public function getProjectDir() : string
    {
        return __DIR__.'/..';
    }

    public function getCacheDir() : string
    {
        return "{$this->getProjectDir()}/var/cache/{$this->environment}";
    }

    public function getLogDir() : string
    {
        return "{$this->getProjectDir()}/var/log";
    }

    public function registerBundles() : iterable
    {
        $contents = require "{$this->getConfigDir()}/bundles.php";
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader) : void
    {
        $container->addResource(new FileResource("{$this->getConfigDir()}/bundles.php"));
        $container->setParameter('container.dumper.inline_class_loader', true);

        $loader->load("{$this->getConfigDir()}/{packages}/*.yaml", 'glob');
        $loader->load("{$this->getConfigDir()}/{packages}/{$this->environment}/**/*.yaml", 'glob');
        $loader->load("{$this->getConfigDir()}/{services}.yaml", 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes) : void
    {
        $routes->import("{$this->getConfigDir()}/{routes}/{$this->environment}/**/*.yaml", '/', 'glob');
        $routes->import("{$this->getConfigDir()}/{routes}.yaml", '/', 'glob');
    }

    private function getConfigDir() : string
    {
        return "{$this->getProjectDir()}/config";
    }
}
