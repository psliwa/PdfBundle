PsPdfBundle
===========

This bundle integrates Symfony2 with [PHPPdf][1] library.

Nowday documentation of [PHPPdf][1] library is only in Polish, be patient, documentation will be also in English. Library has not been completed yet, actually is in development stage as same as this bundle.

Installation
------------

1. Add this bundle and [PHPPdf][1] library to /vendor directory:

    git submodule add git://github.com/psliwa/PdfBundle.git vendor/bundles/Ps/PdfBundle
    git submodule add git://github.com/psliwa/PHPPdf.git vendor/PHPPdf

2. Register bundle and [PHPPdf][1] library in autoloader:

    //app/autoload.php
    $loader->registerNamespaces(array(
        'Ps' => __DIR__.'/../vendor/bundles',
        'PHPPdf' => __DIR__.'/../vendor/PHPPdf/lib',
    ));
    
    $loader->registerPrefixes(array(
        'Zend_' => __DIR__.'/../vendor/PHPPdf/lib/vendor',
    ));

3. Register bundle in AppKernel:

    //app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ..
            new Ps\PdfBundle\PsPdfBundle(),
            // ..
        );
    }

Example
-------
    // In controller
    //...
    use Ps\PdfBundle\Annotation\Pdf;
    //...
    
    /**
     * @Pdf()
     */
    public function helloAction($name)
    {
        $format = $this->get('request')->get('_format');
        
        return $this->render(sprintf('SomeBundle:SomeController:helloAction.%s.twig', $format), array(
            'name' => $name,
        ));
    }
    
    // in helloAction.html.twig
    Hello {{ name }}!
    
    // in helloAction.pdf.twig
    <pdf>
        <dynamic-page>
            Hello {{ name }}!
        </dynamic-page>
    </pdf>
    
Bundle automatically detects pdf format (via _format) request and create pdf document from response.

[1]: https://github.com/psliwa/PHPPdf