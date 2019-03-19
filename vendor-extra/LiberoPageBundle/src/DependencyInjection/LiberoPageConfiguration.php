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
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->values(['content', 'homepage'])
                        ->isRequired()
                    ->end()
                    ->scalarNode('path')
                        ->isRequired()
                    ->end()
                    ->scalarNode('content_service')
                    ->end()
                ->end()
                ->validate()
                    ->ifTrue(function (array $values) : bool {
                        return 'content' === $values['type'] && !isset($values['content_service']);
                    })
                    ->thenInvalid('Content pages require a content_service')
                ->end()
                ->validate()
                    ->ifTrue(function (array $values) : bool {
                        return 'content' !== $values['type'] && isset($values['content_service']);
                    })
                    ->thenInvalid('Non-content pages cannot have a content_service')
                ->end()
            ->end()
        ;
        return $pagesNode;
    }
}
