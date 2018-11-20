<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class ContentPageRouteLoader extends Loader
{
    private $pages;

    public function __construct(array $pages)
    {
        $this->pages = $pages;
    }

    public function load($resource, $type = null) : RouteCollection
    {
        $routes = new RouteCollectionBuilder();

        foreach ($this->pages as $name => $config) {
            $routes->add(
                $config['path'],
                "libero.content_page.controller.content.{$config['name']}",
                "libero.content.{$config['name']}.item"
            );
        }

        return $routes->build();
    }

    public function supports($resource, $type = null) : bool
    {
        return 'content_page' === $type;
    }
}
