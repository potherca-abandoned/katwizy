<?php

namespace Potherca\Katwizy;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Annotations\AnnotationRegistry;

/*/ Autoloader /*/
$scriptFileName = $_SERVER['SCRIPT_FILENAME'];
$webDirectory = dirname($scriptFileName);
$rootDirectory = dirname($webDirectory);
$loader = require $rootDirectory.'/vendor/autoload.php';

/*/ Autoload annotations /*/
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

/*/ Run Framework /*/
$request = Request::createFromGlobals();
$kernel = new AppKernel(
    $rootDirectory,
    [/* @TODO: Grab config from the environment */
        AppKernel::ENVIRONMENT => AppKernel::DEVELOPMENT,
        AppKernel::DEBUG => true
    ]
);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

/*EOF*/
