<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is LICENSE file.
 */

namespace Ps\PdfBundle\Reflection;

/**
 * Simple factory method for reflection objects created in order to testing.
 * 
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class Factory
{
    public function createMethod($objectOrClass, $methodName)
    {
        return new \ReflectionMethod($objectOrClass, $methodName);
    }
}