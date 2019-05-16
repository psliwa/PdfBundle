<?php

namespace Ps\PdfBundle\Tests\DependencyInjection;

use PHPPdf\Cache\Cache;
use PHPPdf\Core\Facade;
use PHPPdf\Core\FacadeBuilder;
use PHPUnit\Framework\TestCase;
use Ps\PdfBundle\DependencyInjection\PsPdfExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PsPdfExtensionTest extends TestCase
{
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new PsPdfExtension();
    }

    /**
     * @test
     */
    public function insertFacadeObjectIntoContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.cache_dir', __DIR__.'/');

        $this->extension->load([], $container);

        $this->assertTrue($container->has('ps_pdf.facade'));
        $facade = $container->get('ps_pdf.facade');

        $this->assertInstanceOf(Facade::class, $facade);
    }

    /**
     * @test
     */
    public function insertFacadeBuilderObjectIntoContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.cache_dir', __DIR__.'/');

        $this->extension->load([], $container);

        $this->assertTrue($container->has('ps_pdf.facade_builder'));
        $builder = $container->get('ps_pdf.facade_builder');

        $this->assertInstanceOf(FacadeBuilder::class, $builder);
    }

    /**
     * @test
     */
    public function insertCacheObjectIntoContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.cache_dir', __DIR__.'/');

        $this->extension->load([], $container);

        $this->assertTrue($container->has('ps_pdf.cache'));
        $cache = $container->get('ps_pdf.cache');

        $this->assertInstanceOf(Cache::class, $cache);
    }

    /**
     * @test
     */
    public function setContainerParametersIfPassed()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.cache_dir', __DIR__.'/');
        $config = [
            [
                'nodes_file' => 'nodes file',
                'fonts_file' => 'some file',
                'complex_attributes_file' => 'some another file',
                'colors_file' => 'colors file',
                'cache' => [
                    'type' => 'some type',
                    'options' => [
                        'custom_option' => 'value',
                    ],
                ],
                'use_cache_in_stylesheet' => true,
                'markdown_stylesheet_filepath' => 'path1',
                'markdown_document_template_filepath' => 'path2',
                'document_parser_type' => 'markdown',
            ],
        ];

        $this->extension->load($config, $container);

        $this->assertEquals($config[0]['nodes_file'], $container->getParameter('ps_pdf.nodes_file'));
        $this->assertEquals($config[0]['colors_file'], $container->getParameter('ps_pdf.colors_file'));
        $this->assertEquals($config[0]['fonts_file'], $container->getParameter('ps_pdf.fonts_file'));
        $this->assertEquals($config[0]['complex_attributes_file'], $container->getParameter('ps_pdf.complex_attributes_file'));
        $this->assertEquals($config[0]['cache']['type'], $container->getParameter('ps_pdf.cache.type'));
        $this->assertEquals($config[0]['cache']['options'], $container->getParameter('ps_pdf.cache.options'));
        $this->assertEquals($config[0]['use_cache_in_stylesheet'], $container->getParameter('ps_pdf.use_cache_in_stylesheet'));
        $this->assertEquals($config[0]['markdown_stylesheet_filepath'], $container->getParameter('ps_pdf.markdown_stylesheet_filepath'));
        $this->assertEquals($config[0]['markdown_document_template_filepath'], $container->getParameter('ps_pdf.markdown_document_template_filepath'));
        $this->assertEquals($config[0]['document_parser_type'], $container->getParameter('ps_pdf.document_parser_type'));
    }
}
