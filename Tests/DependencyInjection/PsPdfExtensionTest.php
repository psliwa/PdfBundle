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
        $config = array(
            array(
                'glyph_file' => 'some file',
                'enhancement_file' => 'some another file',
            ),
        );

        $this->extension->load($config, $container);
        
        $this->assertEquals($config[0]['glyph_file'], $container->getParameter('ps_pdf.glyph_file'));
        $this->assertEquals($config[0]['enhancement_file'], $container->getParameter('ps_pdf.enhancement_file'));
    }
}