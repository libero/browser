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
                ->append($this->getInfoBarDefinition())
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
                ->scalarNode('primary_listing')
                    ->isRequired()
                ->end()
                ->append($this->getHomepageHeaderImageDefinition())
            ->end()
        ;
        return $pagesNode;
    }

    private function getHomepageHeaderImageDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $headerImageNode */
        $headerImageNode = $builder->root('header_image');
        $headerImageNode
            ->children()
                ->scalarNode('src')
                    ->isRequired()
                ->end()
                ->arrayNode('sources')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('srcset')
                                ->isRequired()
                            ->end()
                            ->scalarNode('media')
                            ->end()
                            ->scalarNode('type')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $headerImageNode;
    }

    private function getContentPagesDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $pagesNode */
        $pagesNode = $builder->root('content');
        $pagesNode
            ->normalizeKeys(false)
            ->arrayPrototype()
                ->children()
                    ->scalarNode('path')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
        ;
        return $pagesNode;
    }

    private function getInfoBarDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $infoBarNode */
        $infoBarNode = $builder->root('info_bar');
        $infoBarNode
            ->children()
                ->scalarNode('text')
                    ->isRequired()
                ->end()
            ->end()
        ;
        return $infoBarNode;
    }
}
