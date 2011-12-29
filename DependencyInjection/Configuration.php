<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration definition class
 * 
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ps_pdf');
        
        $rootNode->children()
                    ->arrayNode('cache')
                      ->children()
                        ->variableNode('options')
                          ->defaultValue(array())
                        ->end()
                        ->scalarNode('type')
                          ->defaultValue('File')
                        ->end()
                      ->end()
                    ->end()
                    ->scalarNode('use_cache_in_stylesheet')
                      ->defaultValue(true)
                    ->end()
                    ->scalarNode('nodes_file')
                      ->defaultNull()
                    ->end()
                    ->scalarNode('fonts_file')
                      ->defaultNull()
                    ->end()
                    ->scalarNode('complex_attributes_file')
                      ->defaultNull()
                    ->end()
                    ->scalarNode('colors_file')
                      ->defaultNull()
                    ->end()
                    ->scalarNode('markdown_stylesheet_filepath')
                      ->defaultNull()
                    ->end()
                    ->scalarNode('markdown_document_template_filepath')
                      ->defaultNull()
                    ->end()
                    ->scalarNode('document_parser_type')
                      ->defaultValue('xml')
                    ->end()
                  ->end();

        return $treeBuilder;
    }
}