<?php

namespace Ps\PdfBundle\Tests\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Ps\PdfBundle\Annotation\Pdf;

class PdfTest extends TestCase
{
    /**
     * @Pdf()
     */
    public function testPdfAnnotationIsCorrectlyCreatedByReader()
    {
        $reader = new AnnotationReader();
        
        $method = new \ReflectionMethod($this, 'testPdfAnnotationIsCorrectlyCreatedByReader');
        $pdf = $reader->getMethodAnnotation($method, Pdf::class);
        
        $this->assertNotNull($pdf);
    }
    
    /**
     * @test
     * @dataProvider createAnnotationProvider
     */
    public function createAnnotation(array $args, $expectedException)
    {
        try
        {
            $defaultArgs = array('stylesheet' => null, 'documentParserType' => 'xml', 'headers' => array(), 'enableCache' => false);
            
            $annotation = new Pdf($args);
            
            if($expectedException)
            {
                $this->fail('exception expected');
            }
            
            $expectedVars = $args + $defaultArgs;
            
            $this->assertEquals($expectedVars, get_object_vars($annotation));
        }
        catch(\InvalidArgumentException $e)
        {
            if(!$expectedException)
            {
                $this->fail('unexpected exception');
            }
        }
    }
    
    public function createAnnotationProvider()
    {
        return array(
            array(array(), false),
            array(array('stylesheet' => 'stylesheet', 'documentParserType' => 'markdown'), false),
            array(array('enableCache' => true, 'headers' => array('key' => 'value')), false),
            array(array('unexistedArg' => 'value'), true),
        );
    }
}
