<?php

/*
 * Copyright 2011 Piotr Sliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\Templating;

use Symfony\Component\HttpKernel\Kernel;

/**
 * Image locator
 * 
 * @author Piotr Sliwa <peter.pl7@gmail.com>
 */
class ImageLocator
{
    private $kernel;
    
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
    
    /**
     * Converts image logical name in "BundleName:image-name.extension" format to absolute file path.
     * 
     * @return string file path
     * 
     * @throws InvalidArgumentException If bundle does not exist.
     */
    public function getImagePath($logicalImageName)
    {
        $pos = strpos($logicalImageName, ':');

        $bundleName = substr($logicalImageName, 0, $pos);  
        $imageName = substr($logicalImageName, $pos + 1);
        
        // add support for ::$imagePath syntax as in twig
        // @see http://symfony.com/doc/current/book/page_creation.html#optional-step-3-create-the-template
        if (empty($bundleName)) {
            return $this->kernel->getRootDir() . '/Resources/public/images/' . $imageName;
        }
        
        $bundle = $this->kernel->getBundle($bundleName);
        $bundlePath = $bundle->getPath();
        
        return $bundlePath.'/Resources/public/images/'.$imageName;
    }
}