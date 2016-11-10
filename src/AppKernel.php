<?php

namespace Potherca\Katwizy;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @FIXME: Default config is no longer loaded. Default config files should be available in Katwizy that can be `include`'ed from project config files.
 * @FIXME: Various hard-coded values for directories need to be read from a config file!
 * @TODO: Add arbitrary Twig template loading: $container->get('twig.loader')->addPath('/some/path/with/templates/');
 */
class AppKernel extends Kernel
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const DEVELOPMENT = 'dev';
    const PRODUCTION = 'prod';

    /** @var ConfigLoader */
    private $configLoader;

    use MicroKernelTrait;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @return string */
    final public function getCacheDir()
    {
        return $this->getVarDir().'/cache';
    }

    /** @return string */
    final public function getLogDir()
    {
        return $this->getVarDir().'/logs';
    }

    /** @return string */
    final public function getProjectDir()
    {
        return $this->configLoader->getProjectDir();
    }

    /** @return string */
    final public function getSourceDir()
    {
        return $this->getProjectDir().'/src';
    }

    /** @return string */
    final public function getVarDir()
    {
        return $this->configLoader->getVarDir();
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function __construct(
        ConfigLoader $configurationLoader,
        $environment,
        $debug
    ) {
        $this->configLoader = $configurationLoader;

        parent::__construct($environment, $debug);
    }

    /**
     * @return array<BundleInterface>
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    final public function registerBundles()
    {
        $bundles = [];

        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $bundleConfig = [
            self::PRODUCTION => [
                \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class,
                \Symfony\Bundle\SecurityBundle\SecurityBundle::class,
                \Symfony\Bundle\TwigBundle\TwigBundle::class,
                \Symfony\Bundle\MonologBundle\MonologBundle::class,
                \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class,
                \Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class,
                \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class,
                \AppBundle\AppBundle::class,
            ],
            self::DEVELOPMENT => [
                \Symfony\Bundle\DebugBundle\DebugBundle::class,
                \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class,
                \Sensio\Bundle\DistributionBundle\SensioDistributionBundle::class,
                \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle::class,
            ],
        ];

        $projectBundleConfig = $this->configLoader->loadBundles();

        $bundleConfig = array_merge_recursive($bundleConfig, $projectBundleConfig);

        $loadBundles = function ($bundle) use (&$bundles) {
            $bundles[] = new $bundle;
        };

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            array_walk($bundleConfig[self::DEVELOPMENT], $loadBundles);
        }

        array_walk($bundleConfig[self::PRODUCTION], $loadBundles);

        return $bundles;
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @param LoaderInterface $loader
     *
     * @throws \LogicException
     * @throws \Exception
     */
    final public function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader)
    {
        $defaultConfig = [
            'templating' => [
                'engines' => ['twig'],
            ],
            'profiler' => [
                'only_exceptions' =>  false,
            ],
            'session' => [
                'handler_id' => 'session.handler.native_file',
                'save_path'  => $this->getVarDir() . '/sessions',
            ]
        ];

        $this->configLoader->loadDefaultConfiguration($containerBuilder, $defaultConfig);
        $this->configLoader->loadProjectParameters($loader);
        $this->configLoader->loadDefaultSecurity($loader);
        $this->configLoader->loadProjectConfig($loader);
        $this->configLoader->loadRequiredConfig($containerBuilder);

        /*/ configure WebProfilerBundle if it is enabled /*/
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
        //$routes->add('/', 'kernel:homeAction', 'Homepage');

        // import the WebProfilerRoutes if the bundle is enabled
        if (isset($this->bundles['WebProfilerBundle'])) {
            $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml', '/_wdt');
            $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml', '/_profiler');
        }

        /*/ load routes from source annotations /*/
        if (is_dir($this->getSourceDir())) {
            $routes->import($this->getSourceDir(), '/', 'annotation');
        }

        /*/ load routes from web annotations /*/
        if (is_dir($this->getProjectDir().'/web/')) {
            //@FIXME: Web root may not be `web` but `www`, `public` or heavens knows what.
            $routes->import($this->getProjectDir().'/web/', '/', 'annotation');
        }

        $this->configLoader->loadRoutes($routes);

    }
}

/*EOF*/
