<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class LiberoPageConfiguration implements ConfigurationInterface
{
    private $rootName;

    public function __construct(string $rootName)
    {
        $this->rootName = $rootName;
    }

    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root($this->rootName);
        $rootNode
            ->fixXmlConfig('page')
            ->children()
                ->append($this->getPagesDefinition())
                ->scalarNode('client')
                    ->isRequired()
                ->end()
                ->scalarNode('page_template')
                    ->isRequired()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }

    private function getPagesDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $pagesNode */
        $pagesNode = $builder->root('pages');
        $pagesNode
            ->children()
                ->append($this->getHomepageDefinition())
                ->append($this->getContentPagesDefinition())
            ->end()
        ;
        return $pagesNode;
    }

    private function getHomepageDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $pagesNode */
        $pagesNode = $builder->root('homepage');
        $pagesNode
            ->children()
                ->scalarNode('path')
                    ->isRequired()
                ->end()
                ->scalarNode('search_service')
                    ->isRequired()
                ->end()
            ->end()
        ;
        return $pagesNode;
    }

    private function getContentPagesDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $pagesNode */
        $pagesNode = $builder->root('content');
        $pagesNode
            ->arrayPrototype()
                ->children()
                    ->scalarNode('path')
                        ->isRequired()
                    ->end()
                    ->scalarNode('content_service')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
        ;
        return $pagesNode;
    }
}
