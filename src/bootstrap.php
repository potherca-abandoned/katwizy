<?php

namespace Potherca\Katwizy;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Annotations\AnnotationRegistry;

if ($loader === null) {
    /*/ Autoloader /*/
    $scriptFileName = $_SERVER['SCRIPT_FILENAME'];
    $webDirectory = dirname($scriptFileName);
    $rootDirectory = dirname($webDirectory);

    $loader = require $rootDirectory.'/vendor/autoload.php';
}

$reflector = new \ReflectionObject($loader);
// Autoloader at /vendor/composer/ClassLoader.php
$rootDirectory = dirname(dirname(dirname($reflector->getFileName())));

/*/ Autoload annotations /*/
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

/*/ Run Framework /*/
$request = Request::createFromGlobals();
$kernel = new AppKernel($rootDirectory, [
    AppKernel::ENVIRONMENT => AppKernel::DEVELOPMENT,// @TODO: Grab config from the environment
    AppKernel::DEBUG => true // @TODO: Match debugtoken from cookie/header/url to config:debugtoken
]);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

/*EOF*/
