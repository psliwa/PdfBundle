<?php

namespace Ps\PdfBundle\Test\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Ps\PdfBundle\Annotation\Pdf;

class PdfTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @Pdf()
     */
    public function testPdfAnnotationIsCorrectlyCreatedByReader()
    {
        $reader = new AnnotationReader();
        
        $method = new \ReflectionMethod($this, 'testPdfAnnotationIsCorrectlyCreatedByReader');
        $pdf = $reader->getMethodAnnotation($method, 'Ps\PdfBundle\Annotation\Pdf');
        
        $this->assertNotNull($pdf);
    }
}