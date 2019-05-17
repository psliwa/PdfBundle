<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\Controller;

use Ps\PdfBundle\Annotation\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller with examples.
 *
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class ExampleController extends Controller
{
    public function indexAction()
    {
        return $this->render('PsPdfBundle:Example:index.html.twig');
    }

    public function usingFacadeDirectlyAction()
    {
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('PsPdfBundle:Example:usingFacadeDirectly.pdf.twig', [], $response);

        $xml = $response->getContent();

        $content = $facade->render($xml);

        return new Response($content, 200, ['content-type' => 'application/pdf']);
    }

    /**
     * Possible custom headers and external stylesheet file.
     *
     * @Pdf(
     * 	headers={"Expires"="Sat, 1 Jan 2000 12:00:00 GMT"},
     * 	stylesheet="PsPdfBundle:Example:pdfStylesheet.xml.twig",
     *  enableCache=true
     * )
     */
    public function usingAutomaticFormatGuessingAction($name)
    {
        $format = $this->get('request')->get('_format');

        return $this->render(sprintf('PsPdfBundle:Example:usingAutomaticFormatGuessing.%s.twig', $format), [
            'name' => $name,
        ]);
    }

    /**
     * Standard examples of PHPPdf library.
     */
    public function examplesAction()
    {
        $kernelRootDir = $this->container->getParameter('kernel.root_dir');

        $kernelProjectDir = $this->container->getParameter('kernel.project_dir');
        $propablyPhpPdfExamplesFilePaths = [$kernelProjectDir.'/vendor/PHPPdf/examples/index.php', $kernelProjectDir.'/vendor/psliwa/php-pdf/examples/index.php'];

        foreach ($propablyPhpPdfExamplesFilePaths as $propablyPhpPdfExamplesFilePath) {
            if (file_exists($propablyPhpPdfExamplesFilePath)) {
                require $propablyPhpPdfExamplesFilePath;
                exit();
            }
        }

        throw new NotFoundHttpException('File with PHPPdf examples not found.');
    }

    /**
     * @Pdf(
     * 	headers={"Expires"="Sat, 1 Jan 2000 12:00:00 GMT"},
     *  documentParserType="markdown"
     * )
     */
    public function markdownAction()
    {
        $format = $this->get('request')->get('_format');

        return $this->render(sprintf('PsPdfBundle:Example:markdown.%s.twig', $format));
    }
}
