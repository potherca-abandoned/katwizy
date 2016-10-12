<?php

namespace Dealerdirect\Symfony\KaTwizy;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollectionBuilder;

require dirname(__DIR__).'/vendor/autoload.php';

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    final public function registerBundles()
    {
        return [
            new FrameworkBundle()
        ];
    }

    final public function getLogDir()
    {
        return dirname(__DIR__).'/var/'.$this->environment.'/logs';
    }

    final public function getCacheDir()
    {
        return dirname(__DIR__).'/var/'.$this->environment.'/cache';
    }

    final public function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader)
    {
        // PHP equivalent of config.yml
        $containerBuilder->loadFromExtension('framework', array(
            'secret' => 'S0ME_SECRET'
        ));
    }

    final public function configureRoutes(RouteCollectionBuilder $routes)
    {
        // kernel is a service that points to this class
        // optional 3rd argument is the route name
        $routes->add('/random/{limit}', 'kernel:randomAction');
        //$routes->add('/', 'kernel:homeAction', 'Homepage');
    }

    // @TODO: Move Controller Functions To A Controller
    final public function homeAction()
    {
        return new Response('Hello!');
    }

    final public function randomAction($limit)
    {
        return new JsonResponse(array(
            'number' => mt_rand(0, $limit)
        ));
    }

}

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();

try {
    $response = $kernel->handle($request);
} catch (NotFoundHttpException $resourceNotFoundException) {
    $response = new Response(
        sprintf('Could not find route for "%s %s"', $request->getMethod(), $request->getPathInfo()),
        Response::HTTP_NOT_FOUND
    );
} catch (ResourceNotFoundException $resourceNotFoundException) {
    $response = new Response(
        sprintf('Could not find route for "%s %s"', $request->getMethod(), $request->getPathInfo()),
        Response::HTTP_NOT_FOUND
    );
} catch (\Exception $exception) {
    $response = new Response(
        sprintf('An error occurred. (%s)', get_class($exception)),
        Response::HTTP_INTERNAL_SERVER_ERROR
    );
}

$response->send();
$kernel->terminate($request, $response);



/*EOF*/
