<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace Ps\PdfBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Pdf annotation.
 *
 * @Annotation
 *
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class Pdf
{
    public $stylesheet;
    public $documentParserType = 'xml';
    public $headers = [];
    public $enableCache = false;

    public function __construct(array $values)
    {
        $currentValues = get_object_vars($this);

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $currentValues)) {
                $this->$key = $value;
            } else {
                throw new \InvalidArgumentException(sprintf('Argument "%s" for @Pdf() annotation is unsupported.', $key));
            }
        }
    }
}
