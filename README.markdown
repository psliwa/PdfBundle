PsPdfBundle
===========

This bundle integrates Symfony2 with [PHPPdf][1] library.

This branch is compatible with Zend Framework in 1.11 version. If you wish to use ZF2, you should be interested in master branch.

Documentation of [PHPPdf][1] you can find on github (README file).

Installation
------------

  1. Add this bundle and [PHPPdf][1] library to deps file:

          [PdfBundle]
              git=git://github.com/psliwa/PdfBundle.git
              target=/bundles/Ps/PdfBundle
          [PHPPdf]
              git=git://github.com/psliwa/PHPPdf.git

  2. Download dependencies (for example Zend_Pdf component) of PHPPdf library. You can skip this step, if your application has had dependency on ZF2 framework already.
  
          php vendor/PHPPdf/vendors.php

  3. Register bundle and [PHPPdf][1] library in autoloader:

          //app/autoload.php
          $loader->registerNamespaces(array(
              'Ps' => __DIR__.'/../vendor/bundles',
              'PHPPdf' => __DIR__.'/../vendor/PHPPdf/lib',
              'Zend' => __DIR__.'/../vendor/PHPPdf/lib/vendor',//If you have used ZF2 packages already, you should skip this entry
          ));

  4. Register bundle in AppKernel:

          //app/AppKernel.php
          public function registerBundles()
          {
              return array(
                  // ..
                  new Ps\PdfBundle\PsPdfBundle(),
                  // ..
              );
          }

Configuration
-------------

All options are optional.

    # app/config/config.yml
    ps_pdf:
        nodes_file: ~
        fonts_file: ~
        complex_attributes_file: ~
        colors_file: ~
        use_cache_in_stylesheet: ~
        cache:
          type: ~
          options: ~
        markdown_stylesheet_filepath: ~
        markdown_document_template_filepath: ~
        document_parser_type: ~

* nodes_file - path to file with nodes/tags definitions, internal nodes.xml file from PHPPdf library is used by default
* fonts_file - path to file with fonts definitions, internal fonts.xml file from PHPPdf library is used by default
* complex_attributes_file - path to file with complex attributes definitions, internal complex-attributes.xml file from PHPPdf library is used by default
* colors_file - path to file with default palette of colors, internal colors.xml file from PHPPdf library is used by default
* cache.type - type of cache, supported are all backend cache from Zend_Cache component (for instance File, Apc, Memcached, Sqlite etc.). File engine is used by default.
* cache.options - specyfic options for cache engine (for instance "cache_dir" for File engine). cache_dir by default is as same as kernel.cache_dir.
* use_cache_in_stylesheet - stylesheet maching rules will be cache, if this option is set. In complex stylesheet cache significantly improves performance. Default is true, but **in dev environment cache should be off**.
* markdown_stylesheet_filepath - filepath of stylesheet for markdown parser
* markdown_document_template_filepath - xml document template form output of markdown parser
* document_parser_type - default parser type: xml or markdown

Images in source document
-------------------------

If you want to display image, you must provide absolute path to image file via "src" attribute of image tag. Asset Twig function dosn't work, because it converts image path to relative path according to web directory. To make using of images easier, bundle provides Twig function, that converts image logical name to real, absolute path.

Example:

    <pdf>
        <dynamic-page>
            <!-- pdf_image('BundleName:image-name.extension') -->
            <img src="{{ pdf_image('SymfonyWebConfiguratorBundle:blue-arrow.png') }}" />
        </dynamic-page>
    </pdf>

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

Pdf annotation has four optional properties:

* headers - associative array of specyfic headers
* stylesheet - pdf stylesheet template file in standard Symfony2 notation ("Bundle:Controller:file.format.engine")
* documentParserType - type of parser: xml or markdown
* enableCache - pdf output should by cached? True or false, default: false. Hash (md5) from template and stylesheet content is a cache key, only PHPPdf invocation is cached, controller is always called.

[1]: https://github.com/psliwa/PHPPdf