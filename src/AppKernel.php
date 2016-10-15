<?php

namespace Potherca\Katwizy;

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

class AppKernel extends Kernel
{
    const DEBUG = 'debug';
    const DEVELOPMENT = 'dev';
    const ENVIRONMENT = 'environment';
    const PRODUCTION = 'prod';

    use MicroKernelTrait;

    final public function __construct($projectPath, array $options = [])
    {
        $this->projectPath = $projectPath;

        $options = array_merge(
            [self::ENVIRONMENT => self::PRODUCTION, self::DEBUG => false],
            $options
        );
        parent::__construct($options[self::ENVIRONMENT], $options[self::DEBUG]);
    }

    final public function registerBundles()
    {
        //@FIXME: Also load (other) packages from the project configuration
        $bundles = [];

        $productionBundles = [
            \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class,
            \Symfony\Bundle\TwigBundle\TwigBundle::class,
            \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class,
        ];

        $developmentBundles = [
            \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class,
        ];

        $loadBundles = function ($bundle) use (&$bundles) {
            $bundles[] = new $bundle;
        };

        if ($this->getEnvironment() == 'dev') {
            array_walk($developmentBundles, $loadBundles);
        }

        array_walk($productionBundles, $loadBundles);

        return $bundles;
    }

    final public function getVarDir()
    {
        return $this->projectPath.'/var/'.$this->environment;
    }

    final public function getLogDir()
    {
        return $this->getVarDir().'/logs';
    }

    final public function getCacheDir()
    {
        return $this->getVarDir().'/cache';
    }

    public function getRootDir()
    {
        return $this->projectPath.'/src/';
    }

    final public function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader)
    {
        // PHP equivalent of `config.yml`
        $containerBuilder->loadFromExtension('framework', [
            'secret' => 'S0ME_SECRET',
            'templating' => [
                'engines' => ['twig'],
            ],
            'profiler' => [
                "only_exceptions" =>  false,
            ],
        ]);

        if (is_readable($this->projectPath.'/config/config.yml')) {
            $loader->load($this->projectPath.'/config/config.yml');
        }

        /*/ configure WebProfilerBundle only if the bundle is enabled /*/
        if (isset($this->bundles['WebProfilerBundle'])) {
            $containerBuilder->loadFromExtension('web_profiler', array(
                'toolbar' => true,
                'intercept_redirects' => false,
            ));
        }
    }

    final public function configureRoutes(RouteCollectionBuilder $routes)
    {
        // 'kernel' is the name of a service that points to this class

        // optional 3rd argument is the route name
        //$routes->add('/random/{limit}', 'kernel:randomAction');
        //$routes->add('/', 'kernel:homeAction', 'Homepage');

        // import the WebProfilerRoutes, only if the bundle is enabled
        if (isset($this->bundles['WebProfilerBundle'])) {
            $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml', '/_wdt');
            $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml', '/_profiler');
        }

        /*/ load the annotation routes /*/
        $routes->import($this->projectPath.'/src/Controller/', '/', 'annotation');
    }
}

/*EOF*/