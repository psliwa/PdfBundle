<?php

namespace Ps\PdfBundle\Tests\EventListener;

use Doctrine\Common\Annotations\Reader;
use PHPPdf\Cache\Cache;
use PHPPdf\Core\Facade;
use PHPPdf\Core\FacadeBuilder;
use PHPPdf\Parser\Exception\ParseException;
use PHPUnit\Framework\TestCase;
use Ps\PdfBundle\Annotation\Pdf;
use Ps\PdfBundle\EventListener\PdfListener;
use Ps\PdfBundle\Reflection\Factory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Templating\EngineInterface;

class PdfListenerTest extends TestCase
{
    private $pdfFacadeBuilder;
    private $pdfFacade;
    private $annotationReader;
    private $listener;
    private $controllerEvent;
    private $request;
    private $requestAttributes;
    private $reflactionFactory;
    private $templatingEngine;
    private $cache;

    protected function setUp(): void
    {
        $this->pdfFacadeBuilder = $this->getMockBuilder(FacadeBuilder::class)
                                       ->disableOriginalConstructor()
                                       ->setMethods(['build', 'setDocumentParserType'])
                                       ->getMock();

        $this->pdfFacade = $this->getMockBuilder(Facade::class)
                                ->disableOriginalConstructor()
                                ->setMethods(['render'])
                                ->getMock();

        $this->templatingEngine = $this->getMockBuilder(EngineInterface::class)
                                       ->setMethods(['render', 'supports', 'exists'])
                                       ->getMock();

        $this->reflactionFactory = $this->getMockBuilder(Factory::class)
                                        ->setMethods(['createMethod'])
                                        ->getMock();
        $this->annotationReader = $this->getMockBuilder(Reader::class)
                                       ->setMethods(['getMethodAnnotations', 'getMethodAnnotation', 'getClassAnnotations', 'getClassAnnotation', 'getPropertyAnnotations', 'getPropertyAnnotation'])
                                       ->getMock();

        $this->cache = $this->getMockBuilder(Cache::class)->getMock();

        $this->listener = new PdfListener($this->pdfFacadeBuilder, $this->annotationReader, $this->reflactionFactory, $this->templatingEngine, $this->cache);

        $this->request = $this->getMockBuilder(Request::class)
                              ->setMethods(['get'])
                              ->getMock();
        $this->requestAttributes = $this->getMockBuilder('stdClass')
                                        ->setMethods(['set', 'get'])
                                        ->getMock();

        $this->request->attributes = $this->requestAttributes;

        $this->controllerEvent = $this->getMockBuilder(FilterControllerEvent::class)
                            ->setMethods(['setController', 'getController', 'getRequest'])
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->controllerEvent->expects($this->any())
                    ->method('getRequest')
                    ->will($this->returnValue($this->request));
    }

    /**
     * @test
     * @dataProvider annotationProvider
     */
    public function setAnnotationObjectToRequestIfRequestFormatIsPdfAndAnnotationExists($annotation, $format, $shouldControllerBeenSet)
    {
        $objectStub = new FileLocator();
        $controllerStub = [$objectStub, 'locate'];
        $methodStub = new \ReflectionMethod($controllerStub[0], $controllerStub[1]);

        $this->request->expects($this->once())
                      ->method('get')
                      ->with('_format')
                      ->will($this->returnValue($format));

        $this->controllerEvent->expects($this->any())
                    ->method('getController')
                    ->will($this->returnValue($controllerStub));

        if ('pdf' == $format) {
            $this->reflactionFactory->expects($this->once())
                                    ->method('createMethod')
                                    ->with($controllerStub[0], $controllerStub[1])
                                    ->will($this->returnValue($methodStub));

            $this->annotationReader->expects($this->once())
                                   ->method('getMethodAnnotation')
                                   ->with($methodStub, 'Ps\PdfBundle\Annotation\Pdf')
                                   ->will($this->returnValue($annotation));
        } else {
            $this->reflactionFactory->expects($this->never())
                                    ->method('createMethod');

            $this->annotationReader->expects($this->never())
                                   ->method('getMethodAnnotation');
        }

        if ($shouldControllerBeenSet) {
            $this->requestAttributes->expects($this->once())
                                    ->method('set')
                                    ->with('_pdf', $annotation);
        } else {
            $this->requestAttributes->expects($this->never())
                                    ->method('set');
        }

        $this->listener->onKernelController($this->controllerEvent);
    }

    public function annotationProvider()
    {
        $annotation = new Pdf([]);

        return [
            [$annotation, 'pdf', true],
            [null, 'pdf', false],
            [$annotation, 'html', false],
        ];
    }

    /**
     * @test
     */
    public function donotInvokePdfRenderingOnViewEventWhenResponseStatusIsError()
    {
        $annotation = new Pdf([]);
        $this->requestAttributes->expects($this->once())
                                ->method('get')
                                ->with('_pdf')
                                ->will($this->returnValue($annotation));

        $responseStub = new Response();
        $responseStub->setStatusCode(300);
        $event = new FilterResponseEventStub($this->request, $responseStub);

        $this->pdfFacadeBuilder->expects($this->never())
                               ->method('build');

        $this->listener->onKernelResponse($event);
    }

    /**
     * @test
     * @dataProvider booleanPairProvider
     */
    public function invokePdfRenderingOnViewEvent($enableCache, $freshCache)
    {
        $annotation = new Pdf(['enableCache' => $enableCache]);
        $this->requestAttributes->expects($this->once())
                                ->method('get')
                                ->with('_pdf')
                                ->will($this->returnValue($annotation));

        $contentStub = 'stub';
        $responseContent = 'controller result stub';
        $responseStub = new Response($responseContent);

        if ($enableCache) {
            $cacheKey = md5($responseContent);
            $this->cache->expects($this->once())
                        ->method('test')
                        ->with($cacheKey)
                        ->will($this->returnValue($freshCache));

            if ($freshCache) {
                $this->cache->expects($this->once())
                            ->method('load')
                            ->with($cacheKey)
                            ->will($this->returnValue($contentStub));
            } else {
                $this->cache->expects($this->never())
                            ->method('load');

                $this->expectPdfFacadeBuilding($annotation);

                $this->pdfFacade->expects($this->once())
                                ->method('render')
                                ->with($responseContent)
                                ->will($this->returnValue($contentStub));

                $this->cache->expects($this->once())
                            ->method('save')
                            ->with($contentStub, $cacheKey);
            }
        } else {
            foreach (['test', 'load', 'save'] as $method) {
                $this->cache->expects($this->never())
                            ->method($method);
            }

            $this->expectPdfFacadeBuilding($annotation);

            $this->pdfFacade->expects($this->once())
                            ->method('render')
                            ->with($responseContent)
                            ->will($this->returnValue($contentStub));
        }

        $event = new FilterResponseEventStub($this->request, $responseStub);

        $this->listener->onKernelResponse($event);

        $response = $event->getResponse();

        $this->assertEquals($contentStub, $response->getContent());
    }

    public function booleanPairProvider()
    {
        return [
            [false, false],
            [true, true],
            [true, false],
        ];
    }

    private function expectPdfFacadeBuilding(Pdf $annotation)
    {
        $this->pdfFacadeBuilder->expects($this->once())
                               ->method('setDocumentParserType')
                               ->with($annotation->documentParserType)
                               ->will($this->returnValue($this->pdfFacadeBuilder));
        $this->pdfFacadeBuilder->expects($this->once())
                               ->method('build')
                               ->will($this->returnValue($this->pdfFacade));
    }

    /**
     * @test
     */
    public function setResponseContentTypeAndRequestFormatOnException()
    {
        $annotation = new Pdf(['enableCache' => false]);
        $this->requestAttributes->expects($this->once())
                                ->method('get')
                                ->with('_pdf')
                                ->will($this->returnValue($annotation));

        $this->expectPdfFacadeBuilding($annotation);

        $exception = new ParseException();

        $this->pdfFacade->expects($this->once())
                        ->method('render')
                        ->will($this->throwException($exception));

        $responseStub = new Response();
        $event = new FilterResponseEventStub($this->request, $responseStub);

        try {
            $this->listener->onKernelResponse($event);
            $this->fail('exception expected');
        } catch (ParseException $e) {
            $this->assertEquals('text/html', $responseStub->headers->get('content-type'));
            $this->assertEquals('html', $this->request->getRequestFormat('pdf'));
        }
    }

    /**
     * @test
     */
    public function useStylesheetFromAnnotation()
    {
        $stylesheetPath = 'some path';

        $annotation = new Pdf(['stylesheet' => $stylesheetPath, 'enableCache' => false]);
        $this->requestAttributes->expects($this->once())
                                ->method('get')
                                ->with('_pdf')
                                ->will($this->returnValue($annotation));

        $stylesheetContent = 'stylesheet content';

        $this->templatingEngine->expects($this->once())
                               ->method('render')
                               ->with($stylesheetPath)
                               ->will($this->returnValue($stylesheetContent));

        $this->pdfFacadeBuilder->expects($this->once())
                               ->method('setDocumentParserType')
                               ->with($annotation->documentParserType)
                               ->will($this->returnValue($this->pdfFacadeBuilder));
        $this->pdfFacadeBuilder->expects($this->once())
                               ->method('build')
                               ->will($this->returnValue($this->pdfFacade));

        $this->pdfFacade->expects($this->once())
                        ->method('render')
                        ->with($this->anything(), $stylesheetContent);

        $event = new FilterResponseEventStub($this->request, new Response());
        $this->listener->onKernelResponse($event);
    }

    /**
     * @test
     */
    public function breakInvocationIfControllerIsEmpty()
    {
        $this->request->expects($this->once())
                      ->method('get')
                      ->with('_format')
                      ->will($this->returnValue('pdf'));

        $this->controllerEvent->expects($this->once())
                    ->method('getController')
                    ->will($this->returnValue([]));

        $this->reflactionFactory->expects($this->never())
                                ->method('createMethod');

        $this->listener->onKernelController($this->controllerEvent);
    }
}

class FilterResponseEventStub extends FilterResponseEvent
{
    private $request;
    private $response;

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
