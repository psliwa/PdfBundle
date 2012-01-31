<?php

$vendorDir = __DIR__.'/../vendor';
require_once $vendorDir.'/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;


$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony' => $vendorDir.'/symfony/src',
    'Doctrine\\Common' => $vendorDir.'/doctrine-common/lib',
    'Ps\\PdfBundle' => __DIR__.'/..',
    'PHPPdf' => $vendorDir.'/PHPPdf/lib',
    'Imagine' => $vendorDir.'/Imagine/lib',
    'Zend' => $vendorDir.'/Zend/library'
));

$loader->register();
