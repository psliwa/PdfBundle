<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is LICENSE file.
 */

namespace Ps\PdfBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use PHPPdf\Parser\Facade;
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
    private $pdfFacade;
    private $annotationReader;
    private $reflectionFactory;
    
    public function __construct(Facade $pdfFacade, Reader $annotationReader, Factory $reflectionFactory)
    {
        $this->pdfFacade = $pdfFacade;
        $this->annotationReader = $annotationReader;
        $this->reflectionFactory = $reflectionFactory;
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
        
        $headers = (array) $annotation->headers;
        foreach($headers as $key => $value)
        {
            $response->headers->set($key, $value);
        }
        
        $pdfFacade = $this->pdfFacade;       
        $content = $pdfFacade->render($response->getContent());

        $response->setContent($content);
    }
}