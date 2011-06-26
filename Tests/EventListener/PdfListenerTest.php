<?php

namespace Ps\PdfBundle\Test\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Ps\PdfBundle\Annotation\Pdf;
use Symfony\Component\Config\FileLocator;
use Ps\PdfBundle\EventListener\PdfListener;

class PdfListenerTest extends \PHPUnit_Framework_TestCase
{
    private $pdfFacade;
    private $annotationReader;
    private $listener;
    private $controllerEvent;
    private $request;
    private $requestAttributes;
    private $reflactionFactory;
    private $templatingEngine;
    
    public function setUp()
    {
        $this->pdfFacade = $this->getMockBuilder('PHPPdf\Parser\Facade')
                                ->disableOriginalConstructor()
                                ->setMethods(array('render'))
                                ->getMock();
                                
        $this->templatingEngine = $this->getMockBuilder('Symfony\Component\Templating\EngineInterface')
                                       ->setMethods(array('render', 'supports', 'exists'))
                                       ->getMock();
        
        $this->reflactionFactory = $this->getMockBuilder('Ps\PdfBundle\Reflection\Factory')
                                        ->setMethods(array('createMethod'))
                                        ->getMock();
        $this->annotationReader = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
                                       ->setMethods(array('getMethodAnnotations', 'getMethodAnnotation', 'getClassAnnotations', 'getClassAnnotation', 'getPropertyAnnotations', 'getPropertyAnnotation'))
                                       ->getMock();

        $this->listener = new PdfListener($this->pdfFacade, $this->annotationReader, $this->reflactionFactory, $this->templatingEngine);
        
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                              ->setMethods(array('get'))
                              ->getMock();
        $this->requestAttributes = $this->getMockBuilder('stdClass')
                                        ->setMethods(array('set', 'get'))
                                        ->getMock();
                                        
        $this->request->attributes = $this->requestAttributes;
        
        $this->controllerEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterControllerEvent')
                            ->setMethods(array('setController', 'getController', 'getRequest'))
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
    public function setControllerOnlyIfRequestFormatIsPdfAndAnnotationExists($annotation, $format, $shouldControllerBeenSet)
    {
        $objectStub = new FileLocator();
        $controllerStub = array($objectStub, 'locate');
        $methodStub = new \ReflectionMethod($controllerStub[0], $controllerStub[1]);

        $this->request->expects($this->once())
                      ->method('get')
                      ->with('_format')
                      ->will($this->returnValue($format));
        
        $this->controllerEvent->expects($this->any())
                    ->method('getController')
                    ->will($this->returnValue($controllerStub));
        
        if($format == 'pdf')
        {
            $this->reflactionFactory->expects($this->once())
                                    ->method('createMethod')
                                    ->with($controllerStub[0], $controllerStub[1])
                                    ->will($this->returnValue($methodStub));
            
            $this->annotationReader->expects($this->once())
                                   ->method('getMethodAnnotation')
                                   ->with($methodStub, 'Ps\PdfBundle\Annotation\Pdf')
                                   ->will($this->returnValue($annotation));
        }
        else
        {
            $this->reflactionFactory->expects($this->never())
                                    ->method('createMethod');
            
            $this->annotationReader->expects($this->never())
                                   ->method('getMethodAnnotation');
        }
                    
        if($shouldControllerBeenSet)
        {
            $this->requestAttributes->expects($this->once())
                                    ->method('set')
                                    ->with('_pdf', $annotation);
        }
        else
        {
            $this->requestAttributes->expects($this->never())
                                    ->method('set');
        }
                    
        $this->listener->onKernelController($this->controllerEvent);
    }
    
    public function annotationProvider()
    {
        $annotation = new Pdf(array());
        
        return array(
            array($annotation, 'pdf', true),
            array(null, 'pdf', false),
            array($annotation, 'html', false),
        );
    }
    
    /**
     * @test
     */
    public function invokePdfRenderingOnViewEvent()
    {
        $annotation = new Pdf(array());
        $this->requestAttributes->expects($this->once())
                                ->method('get')
                                ->with('_pdf')
                                ->will($this->returnValue($annotation));
                                
        $contentStub = 'stub';
        $responseContent = 'controller result stub';
        $responseStub = new Response($responseContent);
        
        $this->pdfFacade->expects($this->once())
                        ->method('render')
                        ->with($responseContent)
                        ->will($this->returnValue($contentStub));
        
        $event = new FilterResponseEventStub($this->request, $responseStub);
                        
        $this->listener->onKernelResponse($event);
        
        $response = $event->getResponse();
        
        $this->assertEquals($contentStub, $response->getContent());
    }
    
    /**
     * @test
     */
    public function useStylesheetFromAnnotation()
    {
        $stylesheetPath = 'some path';
        
        $annotation = new Pdf(array('stylesheet' => $stylesheetPath));
        $this->requestAttributes->expects($this->once())
                                ->method('get')
                                ->with('_pdf')
                                ->will($this->returnValue($annotation));
                                
        $stylesheetContent = 'stylesheet content';
        
        $this->templatingEngine->expects($this->once())
                               ->method('render')
                               ->with($stylesheetPath)
                               ->will($this->returnValue($stylesheetContent));
        
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
                    ->will($this->returnValue(array()));
                    
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