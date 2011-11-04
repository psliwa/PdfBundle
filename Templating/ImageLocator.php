<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\Templating;

use Symfony\Component\HttpKernel\Kernel;

/**
 * Image locator
 * 
 * @author Piotr Śliwa <peter.pl7@gmail.com>
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
        
        $bundle = $this->kernel->getBundle($bundleName);
        
        $bundlePath = $bundle->getPath();
        
        return $bundlePath.'/Resources/public/images/'.$imageName;
    }
}