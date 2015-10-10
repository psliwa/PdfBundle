<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\Twig\Extensions\Extension;

use Ps\PdfBundle\Templating\ImageLocatorInterface;

/**
 * Twig extension
 * 
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class PdfExtension extends \Twig_Extension
{
    private $imageLocator;
    
    public function __construct(ImageLocatorInterface $imageLocator)
    {
        $this->imageLocator = $imageLocator;
    }
    
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('pdf_image', array($this, 'getImagePath')),
        );
    }
    
    public function getName()
    {
        return 'ps_pdf';
    }
    
    public function getImagePath($logicalImageName)
    {
        return $this->imageLocator->getImagePath($logicalImageName);
    }
}
