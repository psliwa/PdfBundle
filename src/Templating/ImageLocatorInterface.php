<?php

namespace Ps\PdfBundle\Templating;

/**
 * Image locator.
 *
 * @author Piotr Sliwa <peter.pl7@gmail.com>
 */
interface ImageLocatorInterface
{
    /**
     * Converts image logical name in "BundleName:image-name.extension" format to absolute file path.
     *
     * @return string file path
     *
     * @throws /InvalidArgumentException If bundle does not exist
     */
    public function getImagePath($logicalImageName);
}
