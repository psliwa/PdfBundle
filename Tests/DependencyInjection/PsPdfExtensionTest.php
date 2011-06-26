<?php

namespace Ps\PdfBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ps\PdfBundle\DependencyInjection\PsPdfExtension;

class PsPdfExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $extension;
    
    public function setUp()
    {
        $this->extension = new PsPdfExtension();
    }
    
    /**
     * @test
     */
    public function insertFactoryObjectIntoContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.cache_dir', '/');
        
        $this->extension->load(array(), $container);
        
        $this->assertTrue($container->has('ps_pdf.facade'));
        $facade = $container->get('ps_pdf.facade');
        
        $this->assertInstanceOf('PHPPdf\Parser\Facade', $facade);
    }
    
    /**
     * @test
     */
    public function setContainerParametersIfPassed()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.cache_dir', '/');
        $config = array(
            array(
                'fonts_file' => 'some file',
                'enhancements_file' => 'some another file',
                'cache' => array(
                    'type' => 'some type',
                    'options' => array(
                        'custom_option' => 'value',
                    ),
                ),
                'use_cache_in_stylesheet' => true,
            ),
        );

        $this->extension->load($config, $container);
        
        $this->assertEquals($config[0]['fonts_file'], $container->getParameter('ps_pdf.fonts_file'));
        $this->assertEquals($config[0]['enhancements_file'], $container->getParameter('ps_pdf.enhancements_file'));
        $this->assertEquals($config[0]['cache']['type'], $container->getParameter('ps_pdf.cache.type'));
        $this->assertEquals($config[0]['cache']['options'], $container->getParameter('ps_pdf.cache.options'));
        $this->assertEquals($config[0]['use_cache_in_stylesheet'], $container->getParameter('ps_pdf.use_cache_in_stylesheet'));
    }
}