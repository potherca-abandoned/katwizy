<?php
/**
 * Delivery mechanism for web requests
 */
namespace Potherca\Katwizy;

use Symfony\Component\HttpFoundation\Request;

$autoloadFiles = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(dirname(dirname(__DIR__))) . '/autoload.php'
];

if (file_exists($autoloadFiles[0])) {
    $vendorPath = $autoloadFiles[0];
} elseif (file_exists($autoloadFiles[1])) {
    $vendorPath = $autoloadFiles[1];
} else {
    throw new \RuntimeException('Could not find "vendor/autoload.php". Has `composer install` been run?');
}

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require $vendorPath;
$input = Request::createFromGlobals();

Bootstrap::run($loader, $input);

/*EOF*/
