<?php

namespace Potherca\Katwizy;

$autoloadFiles = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(dirname(dirname(__DIR__))) . '/autoload.php'
];

if (file_exists($autoloadFiles[0])) {
    $loader = require $autoloadFiles[0];
} elseif (file_exists($autoloadFiles[1])) {
    $loader = require $autoloadFiles[1];
} else {
    throw new \RuntimeException('Could not find "vendor/autoload.php". Has `composer install` been run?');
}

Potherca\Katwizy\Bootstrap::run(
    $loader,
    Symfony\Component\HttpFoundation\Request::createFromGlobals()
);

/*EOF*/
