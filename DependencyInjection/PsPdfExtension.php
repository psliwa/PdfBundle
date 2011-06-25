<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is LICENSE file.
 */

namespace Ps\PdfBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PsPdfExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {       
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        
        $extension = new \PHPPdf\Configuration\DependencyInjection\Extension();
        $extension->load(array(), $container);
        
        $loader->load('pdf.xml');
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