<?php

namespace EPS\JqGridBundle\DependencyInjection;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('eps_jq_grid');

        $rootNode
            ->children()
               ->scalarNode('datepicker_format')->defaultValue('dd/mm/yy')->end()
               ->scalarNode('datepickerphp_format')->defaultValue('d/m/y')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
