<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * Extenstion class
 * 
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class PsPdfExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {       
        $config = $this->getConfig($config);
        
        $this->loadDefaults($container);
                
        $this->setConfigIntoContainer($container, $config);
    }
    
    private function getConfig(array $config)
    {
        $configurationProcessor = new Processor();
        $configuration = new Configuration();

        return $configurationProcessor->processConfiguration($configuration, $config);
    }
    
    private function loadDefaults(ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        
        $loader->load('pdf.xml');
    }
    
    private function setConfigIntoContainer(ContainerBuilder $container, array $config)
    {
        $this->setGenericConfig($container, $config, 'ps_pdf.%s', array('nodes_file', 'fonts_file', 'complex_attributes_file', 'colors_file', 'use_cache_in_stylesheet', 'markdown_stylesheet_filepath', 'markdown_document_template_filepath', 'document_parser_type'));

        if(isset($config['cache']))
        {
            $this->setGenericConfig($container, $config['cache'], 'ps_pdf.cache.%s', array('type', 'options'));
        }
    }
    
    private function setGenericConfig(ContainerBuilder $container, array $config, $format, array $options)
    {
        foreach($options as $name)
        {
            if(null !== $config[$name])
            {
                $container->setParameter(sprintf($format, $name), $config[$name]);
            }
        }
    }
    
    public function getNamespace()
    {
        return 'http://ohey.pl/phppdf/schema/dic/'.$this->getAlias();
    }
    
    public function getXsdValidationBasePath()
    {
        return false;
    }
    
    public function getAlias()
    {
        return 'ps_pdf';
    }
}