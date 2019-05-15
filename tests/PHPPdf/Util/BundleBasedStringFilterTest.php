<?php

namespace Ps\PdfBundle\Tests\PHPPdf\Util;

use PHPUnit\Framework\TestCase;
use Ps\PdfBundle\PHPPdf\Util\BundleBasedStringFilter;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class BundleBasedStringFilterTest extends TestCase
{
    private $filter;
    private $kernel;
    
    protected function setUp(): void
    {
        $this->kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $this->filter = new BundleBasedStringFilter($this->kernel);
    }
    
    /**
     * @test
     * @dataProvider replaceBundleVariablesProvider
     */
    public function replaceBundleVariables($string, $expectedString, array $expectedBundles)
    {
        foreach($expectedBundles as $at => $bundle)
        {
            list($bundleName, $bundlePath) = $bundle;
            
            $bundleMock = $this->getMockBuilder(BundleInterface::class)->getMock();
            $bundleMock->expects($this->once())
                       ->method('getPath')
                       ->will($this->returnValue($bundlePath));
            
            $this->kernel->expects($this->at($at))
                         ->method('getBundle')
                         ->with($bundleName)
                         ->will($this->returnValue($bundleMock));
        }
        
        $actualString = $this->filter->filter($string);
        
        $this->assertEquals($expectedString, $actualString);
    }
    
    public function replaceBundleVariablesProvider()
    {
        return array(
            array('some text', 'some text', array()),
            array('text text %SomeBundle:file.xml% text text', 'text text path/Resources/file.xml text text', array(
                array('SomeBundle', 'path'),
            )),
            array('text text %SomeBundle:file1.xml% text %SomeBundle:file2.xml% text', 'text text path/Resources/file1.xml text path/Resources/file2.xml text', array(
                array('SomeBundle', 'path'),
                array('SomeBundle', 'path'),
            )),
        );
    }
}
