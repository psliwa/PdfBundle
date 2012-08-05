<?php

$vendorDir = __DIR__.'/../vendor';
require_once $vendorDir.'/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony' => $vendorDir.'/symfony/src',
    'Doctrine\\Common' => $vendorDir.'/DoctrineCommon/lib',
    'PHPPdf' => $vendorDir.'/PHPPdf/lib',
    'Imagine' => $vendorDir.'/Imagine/lib',
    'Zend' => $vendorDir.'/Zend/library',
	'ZendPdf' => $vendorDir.'/ZendPdf/library',
));

spl_autoload_register(function($class){
    if(strpos($class, 'Ps\PdfBundle') === 0)
    {
        return require_once __DIR__.'/../'.str_replace('\\', '/', substr($class, 13)).'.php';
    }
});

$loader->register();
