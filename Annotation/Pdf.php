<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is LICENSE file.
 */

namespace Ps\PdfBundle\Annotation;

/**
 * Pdf annotation
 * 
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class Pdf
{
    public $stylesheet;
    public $headers = array();

    public function __construct(array $values)
    {
        if(isset($values['stylesheet']))
        {
            $this->stylesheet = $values['stylesheet'];
        }
        
        if(isset($values['headers']))
        {
            $this->headers = $values['headers'];
        }
    }
}