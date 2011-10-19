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
    public function insertFacadeObjectIntoContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.cache_dir', '/');
        
        $this->extension->load(array(), $container);
        
        $this->assertTrue($container->has('ps_pdf.facade'));
        $facade = $container->get('ps_pdf.facade');
        
        $this->assertInstanceOf('PHPPdf\Core\Facade', $facade);
    }
    
    /**
     * @test
     */
    public function insertFacadeBuilderObjectIntoContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.cache_dir', '/');
        
        $this->extension->load(array(), $container);
        
        $this->assertTrue($container->has('ps_pdf.facade_builder'));
        $builder = $container->get('ps_pdf.facade_builder');
        
        $this->assertInstanceOf('PHPPdf\Core\FacadeBuilder', $builder);
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
                'markdown_stylesheet_filepath' => 'path1',
                'markdown_document_template_filepath' => 'path2',
                'document_parser_type' => 'markdown',
            ),
        );

        $this->extension->load($config, $container);
        
        $this->assertEquals($config[0]['fonts_file'], $container->getParameter('ps_pdf.fonts_file'));
        $this->assertEquals($config[0]['enhancements_file'], $container->getParameter('ps_pdf.enhancements_file'));
        $this->assertEquals($config[0]['cache']['type'], $container->getParameter('ps_pdf.cache.type'));
        $this->assertEquals($config[0]['cache']['options'], $container->getParameter('ps_pdf.cache.options'));
        $this->assertEquals($config[0]['use_cache_in_stylesheet'], $container->getParameter('ps_pdf.use_cache_in_stylesheet'));
        $this->assertEquals($config[0]['markdown_stylesheet_filepath'], $container->getParameter('ps_pdf.markdown_stylesheet_filepath'));
        $this->assertEquals($config[0]['markdown_document_template_filepath'], $container->getParameter('ps_pdf.markdown_document_template_filepath'));
        $this->assertEquals($config[0]['document_parser_type'], $container->getParameter('ps_pdf.document_parser_type'));
    }
}