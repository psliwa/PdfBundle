PsPdfBundle
===========

[![Build Status](https://secure.travis-ci.org/psliwa/PdfBundle.png?branch=master)](http://travis-ci.org/psliwa/PdfBundle)

This bundle integrates Symfony2 with [PHPPdf][1] library. Thanks to this bundle you can easily generate PDF or image (png, jpg) files.

Documentation of [PHPPdf][1] you can find on github (README file).

Installation
------------

  1. Use composer. PsPdfBundle requires "minimum-stability" equals to dev. Modify composer.json:

        "require": {
          ...
          "psliwa/pdf-bundle": "~1.0"
        }, 
        
  run `composer update psliwa/pdf-bundle` to install the bundle

  2. Register bundle in AppKernel:

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

Bundle based paths in fonts and document xml file
-------------------------------------------------

If you want to use custom fonts, you should create your own fonts.xml config file (default fonts filepath is PHPPdf\Resources\config\fonts.xml). To make easier defining fonts paths, bundle based paths are supported. Example:

    <!-- some fonts.xml file -->
    <italic src="%SomeBundle:file.ttf%" /> 
    
"%SomeBundle:file.ttf%" will be replaced by "path/to/SomeBundle/Resources/file.ttf"

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
[2]: https://github.com/avalanche123/Imagine
