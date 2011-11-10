<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use PHPPdf\Core\Facade;
use Symfony\Component\HttpKernel\Exception\HttpException;
use PHPPdf\Core\FacadeBuilder;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Ps\PdfBundle\Reflection\Factory;
use Doctrine\Common\Annotations\Reader;

/**
 * This listener will replace reponse content by pdf document's content if Pdf annotations is found.
 * Also adds pdf format to request object and adds proper headers to response object.
 * 
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class PdfListener
{
    private $pdfFacadeBuilder;
    private $annotationReader;
    private $reflectionFactory;
    private $templatingEngine;
    
    public function __construct(FacadeBuilder $pdfFacadeBuilder, Reader $annotationReader, Factory $reflectionFactory, EngineInterface $templatingEngine)
    {
        $this->pdfFacadeBuilder = $pdfFacadeBuilder;
        $this->annotationReader = $annotationReader;
        $this->reflectionFactory = $reflectionFactory;
        $this->templatingEngine = $templatingEngine;
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $request->setFormat('pdf', 'application/pdf');
    }
    
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();        
        $format = $request->get('_format');
        
        if($format != 'pdf' || !($controller = $event->getController()))
        {
            return;
        }
        
        $method = $this->reflectionFactory->createMethod($controller[0], $controller[1]);
        
        $annotation = $this->annotationReader->getMethodAnnotation($method, 'Ps\PdfBundle\Annotation\Pdf');
        
        if($annotation)
        {
            $request->attributes->set('_pdf', $annotation);
        }                
    }
    
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        
        if(!($annotation = $request->attributes->get('_pdf')))
        {
            return;
        }
        
        $response = $event->getResponse();
               
        $stylesheetContent = null;
        if($stylesheet = $annotation->stylesheet)
        {
            $stylesheetContent = $this->templatingEngine->render($stylesheet);
        }
        
        $pdfFacade = $this->pdfFacadeBuilder->setDocumentParserType($annotation->documentParserType)
                                            ->build();

        $content = $this->getPdfContent($pdfFacade, $response, $request, $stylesheetContent);                       

        $headers = (array) $annotation->headers;
        $headers['content-length'] = strlen($content);
        foreach($headers as $key => $value)
        {
            $response->headers->set($key, $value);
        }

        $response->setContent($content);
    }
    
    private function getPdfContent(Facade $pdfFacade, Response $response, Request $request, $stylesheetContent)
    {
        try
        {
            return $pdfFacade->render($response->getContent(), $stylesheetContent);
        }
        catch(\Exception $e)
        {
            $request->setRequestFormat('html');
            $response->headers->set('content-type', 'text/html');
            throw $e;
        }
    }
}