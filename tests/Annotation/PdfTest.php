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
    public function createAnnotation(array $args, bool $expectedException)
    {
        $defaultArgs = [
            'stylesheet' => null,
            'documentParserType' => 'xml',
            'headers' => [],
            'enableCache' => false,
        ];

        if ($expectedException) {
            $this->expectException(\Exception::class);
        }

        $annotation = new Pdf($args);

        $expectedVars = $args + $defaultArgs;

        $this->assertEquals($expectedVars, get_object_vars($annotation));
    }

    public function createAnnotationProvider()
    {
        return [
            [[], false],
            [['stylesheet' => 'stylesheet', 'documentParserType' => 'markdown'], false],
            [['enableCache' => true, 'headers' => ['key' => 'value']], false],
            [['unexistedArg' => 'value'], true],
        ];
    }
}
