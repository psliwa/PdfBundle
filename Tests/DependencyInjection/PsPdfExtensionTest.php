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
}