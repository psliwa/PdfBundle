<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\PHPPdf\Util;

use PHPPdf\Util\StringFilter;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class that provides support for bundle based path translations.
 *
 * Example:
 * %SomeBundle:someFile.xml% will be replaced by "path/to/SomeBundle/Resources/someFile.xml"
 *
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class BundleBasedStringFilter implements StringFilter
{
    private $kernel;

    public function __construct(KernelInterface $kernel = null)
    {
        $this->kernel = $kernel;
    }

    public function filter($value)
    {
        if (!$this->kernel) {
            return $value;
        }

        if (preg_match_all('/\%(.+Bundle):(.+)\%/U', $value, $matches)) {
            $searches = [];
            $replacements = [];
            foreach ($matches[1] as $index => $bundleName) {
                $bundle = $this->kernel->getBundle($bundleName);
                $path = $bundle->getPath();

                $searches[] = $matches[0][$index];
                $replacements[] = $path.'/Resources/'.$matches[2][$index];
            }

            $value = str_replace($searches, $replacements, $value);
        }

        return $value;
    }
}
