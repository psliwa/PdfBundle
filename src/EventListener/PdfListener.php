<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use PHPPdf\Cache\Cache;
use PHPPdf\Core\FacadeBuilder;
use Ps\PdfBundle\Annotation\Pdf as PdfAnnotation;
use Ps\PdfBundle\Reflection\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Templating\EngineInterface;

/**
 * This listener will replace reponse content by pdf document's content if Pdf annotations is found.
 * Also adds proper headers to response object.
 *
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class PdfListener
{
    private $pdfFacadeBuilder;
    private $annotationReader;
    private $reflectionFactory;
    private $templatingEngine;
    private $cache;

    public function __construct(FacadeBuilder $pdfFacadeBuilder, Reader $annotationReader, Factory $reflectionFactory, EngineInterface $templatingEngine, Cache $cache)
    {
        $this->pdfFacadeBuilder = $pdfFacadeBuilder;
        $this->annotationReader = $annotationReader;
        $this->reflectionFactory = $reflectionFactory;
        $this->templatingEngine = $templatingEngine;
        $this->cache = $cache;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $format = $request->get('_format');

        if ('pdf' != $format || !is_array($controller = $event->getController()) || !$controller) {
            return;
        }

        $method = $this->reflectionFactory->createMethod($controller[0], $controller[1]);

        $annotation = $this->annotationReader->getMethodAnnotation($method, 'Ps\PdfBundle\Annotation\Pdf');

        if ($annotation) {
            $request->attributes->set('_pdf', $annotation);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!($annotation = $request->attributes->get('_pdf'))) {
            return;
        }

        $response = $event->getResponse();

        if ($response->getStatusCode() > 299) {
            return;
        }

        $stylesheetContent = null;
        if ($stylesheet = $annotation->stylesheet) {
            $stylesheetContent = $this->templatingEngine->render($stylesheet);
        }

        $content = $this->getPdfContent($annotation, $response, $request, $stylesheetContent);

        $headers = (array) $annotation->headers;
        $headers['content-length'] = strlen($content);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        $response->setContent($content);
    }

    private function getPdfContent(PdfAnnotation $pdfAnnotation, Response $response, Request $request, $stylesheetContent)
    {
        try {
            $responseContent = $response->getContent();

            $pdfContent = null;

            if ($pdfAnnotation->enableCache) {
                $cacheKey = md5($responseContent.$stylesheetContent);

                if ($this->cache->test($cacheKey)) {
                    $pdfContent = $this->cache->load($cacheKey);
                }
            }

            if (null === $pdfContent) {
                $pdfFacade = $this->pdfFacadeBuilder->setDocumentParserType($pdfAnnotation->documentParserType)
                                                    ->build();

                $pdfContent = $pdfFacade->render($responseContent, $stylesheetContent);

                if ($pdfAnnotation->enableCache) {
                    $this->cache->save($pdfContent, $cacheKey);
                }
            }

            return $pdfContent;
        } catch (\Exception $e) {
            $request->setRequestFormat('html');
            $response->headers->set('content-type', 'text/html');
            throw $e;
        }
    }
}
