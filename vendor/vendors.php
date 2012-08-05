<?php

set_time_limit(0);

$deps = array(
    array('symfony', 'git://github.com/symfony/symfony.git', isset($_ENV['SYMFONY_VERSION']) ? $_ENV['SYMFONY_VERSION'] : 'v2.0.9'),
    array('Doctrine\Common', 'git://github.com/doctrine/common.git', isset($_ENV['DOCTRINE_COMMON_VERSION']) ? $_ENV['DOCTRINE_COMMON_VERSION'] : '2.1.4'),
    array('Markdown', 'git://github.com/wolfie/php-markdown.git', 'd464071334'),
    array('Zend', 'git://github.com/zendframework/zf2.git', 'master'),
    array('ZendPdf', 'git://github.com/zendframework/ZendPdf.git', 'master'),
    array('Imagine', 'git://github.com/avalanche123/Imagine.git', 'v0.2.6'),
    array('PHPPdf', 'git://github.com/psliwa/PHPPdf.git', 'origin/1.1.x'),
);

foreach ($deps as $dep) {
    list($name, $url, $rev) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = __DIR__.'/'.$name;
    if (!is_dir($installDir)) {
        system(sprintf('git clone %s %s', $url, $installDir));
    }

    system(sprintf('cd %s && git fetch origin && git reset --hard %s', $installDir, $rev));
}