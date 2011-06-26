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
                    ->scalarNode('glyph_file')
                      ->defaultNull()
                    ->end()
                    ->scalarNode('enhancement_file')
                      ->defaultNull()
                    ->end()
                  ->end();

        return $treeBuilder;
    }
}