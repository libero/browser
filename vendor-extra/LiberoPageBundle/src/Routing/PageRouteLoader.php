<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class PageRouteLoader extends Loader
{
    private $pages;

    public function __construct(array $pages)
    {
        $this->pages = $pages;
    }

    public function load($resource, $type = null) : RouteCollection
    {
        $routes = new RouteCollectionBuilder();

        foreach ($this->pages as $config) {
            $routes->add($config['path'], $config['controller'], $config['route']);
        }

        return $routes->build();
    }

    public function supports($resource, $type = null) : bool
    {
        return 'libero_page' === $type;
    }
}
