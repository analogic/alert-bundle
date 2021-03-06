<?php

namespace Analogic\AlertBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('analogic_alert');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('prefix')->defaultValue("[PANIC] ")->end()
                ->scalarNode('enabled')->defaultTrue()->end()
                ->arrayNode('from')
                    ->children()
                        ->scalarNode('name')->end()
                        ->scalarNode('email')->end()
                    ->end()
                ->end()
                ->variableNode('to')->defaultValue([])->end()
                ->variableNode('javascript_ignore_regex')->defaultValue(null)->end()
                ->variableNode('ignore')->defaultValue([
                    'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
                    'Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException'
                ])->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
