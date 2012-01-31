<?php

$vendorDir = __DIR__.'/../vendor';
require_once $vendorDir.'/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;


$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony' => $vendorDir.'/symfony/src',
    'Doctrine\\Common' => $vendorDir.'/DoctrineCommon/lib',
    'PHPPdf' => $vendorDir.'/PHPPdf/lib',
    'Imagine' => $vendorDir.'/Imagine/lib',
    'Zend' => $vendorDir.'/Zend/library'
));

$directories = array(
	'Annotation', 
	'Controller', 
	'DependencyInjection', 
	'EventListener', 'PHPPdf', 
	'Reflection', 
	'Templating', 
	'Twig',
);

foreach($directories as $dir)
{
    $loader->registerNamespace(sprintf('Ps\\PdfBundle\\%s', $dir), __DIR__.'/..');
}

$loader->register();
