<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
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
        $class = is_object($objectOrClass) ? get_class($objectOrClass) : (string) $objectOrClass;
        
        $class = $this->getUserClass($class);

        return new \ReflectionMethod($class, $methodName);
    }
    
    private function getUserClass($class)
    {
        if(class_exists('CG\Core\ClassUtils', true))
        {
            return \CG\Core\ClassUtils::getUserClass($class);
        }

        return $class;
    }
}