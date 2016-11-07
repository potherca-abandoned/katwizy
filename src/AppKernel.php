<?php

namespace Potherca\Katwizy;

use \Directory;
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
use Symfony\Component\Yaml\Yaml;

/**
 * @FIXME: Various hard-coded values for directories need to be read from a config file!
 * @TODO: Add arbitrary Twig template loading: $container->get('twig.loader')->addPath('/some/path/with/templates/');
 */
class AppKernel extends Kernel
{
    const DEBUG = 'debug';
    const DEVELOPMENT = 'dev';
    const ENVIRONMENT = 'environment';
    const PRODUCTION = 'prod';

    private $projectPath;

    use MicroKernelTrait;

    final public function getCacheDir()
    {
        return $this->getVarDir().'/cache';
    }

    private function getConfigDir()
    {
        return $this->getprojectdir().'/config';
    }

    final public function getLogDir()
    {
        return $this->getVarDir().'/logs';
    }

    final public function getProjectDir()
    {
        return $this->projectPath;
    }

    final public function getSourceDir()
    {
        return $this->getprojectdir().'/src';
    }

    final public function getVarDir()
    {
        return $this->getprojectdir().'/var/'.$this->environment;
    }

    final public function __construct(Directory $projectDirectory, array $options = [])
    {
        $this->projectPath = $projectDirectory->path;

        $options = array_merge(
            [self::ENVIRONMENT => self::PRODUCTION, self::DEBUG => false],
            $options
        );
        parent::__construct($options[self::ENVIRONMENT], $options[self::DEBUG]);
    }

    final public function registerBundles()
    {
        $bundles = [];

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

        /*/ Add bundles from project configuration  /*/
        if (is_readable($this->getConfigDir().'/bundles.yml')) {
            $projectBundleConfig = Yaml::parse(
                file_get_contents($this->getConfigDir().'/bundles.yml')
            );
            $bundleConfig = array_merge_recursive($bundleConfig, $projectBundleConfig);
        }

        $loadBundles = function ($bundle) use (&$bundles) {
            $bundles[] = new $bundle;
        };

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            array_walk($bundleConfig[self::DEVELOPMENT], $loadBundles);
        }

        array_walk($bundleConfig[self::PRODUCTION], $loadBundles);

        return $bundles;
    }

    final public function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader)
    {
        $environment = $this->getEnvironment();

        $defaultConfig = [
            'templating' => [
                'engines' => ['twig'],
            ],
            'profiler' => [
                "only_exceptions" =>  false,
            ],
            'session' => [
                'handler_id' => 'session.handler.native_file',
                'save_path'  => $this->getVarDir() . '/sessions',
            ]
        ];

        $defaultConfigDirectory = $this->getprojectdir().'/vendor/symfony/framework-standard-edition/app/config';
        $projectConfigDirectory = $this->getConfigDir();

        $parametersFile = [
            '/parameters.yml' => '/parameters_'.$environment.'.yml'
        ];
        $defaultConfigFiles = [
            // (?) '/config.yml');
            '/security.yml',
            '/services.yml',
        ];

        $projectConfigFiles =[
            '/config.yml' => '/config_'.$environment.'.yml',
            '/security.yml' => '/security_'.$environment.'.yml',
            '/services.yml' => '/services_'.$environment.'.yml',
        ];

        $loadConfigIfExists = function ($file, $alternativeFile, $directory) use (&$loader) {
            if (is_readable($directory.$file)) {
                $loader->load($directory.$file);
            } elseif (is_numeric($alternativeFile) === false
                && is_readable($directory.$alternativeFile)
            ) {
                $loader->load($directory.$alternativeFile);
            }
        };

        /* Load default configuration */
        $containerBuilder->loadFromExtension('framework', $defaultConfig);
        /* Load project parameters file */
        array_walk($parametersFile, $loadConfigIfExists, $defaultConfigDirectory);
        /* Load default configuration files */
        array_walk($defaultConfigFiles, $loadConfigIfExists, $defaultConfigDirectory);
        /* Load project configuration files */
        array_walk($projectConfigFiles, $loadConfigIfExists, $projectConfigDirectory);

        /*/ Make sure required configuration is set /*/
        $configuration = $this->loadedConfigurations($containerBuilder, 'framework');
        if (array_key_exists('secret', $configuration) === false) {
            $containerBuilder->loadFromExtension('framework', ['secret' => 'S0ME_SECR3T']);
        }

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
        if (is_dir($this->getprojectdir().'/web/')) {
            //@FIXME: Web root may not be `web` but `www`, `public` or heavens knows what.
            $routes->import($this->getprojectdir().'/web/', '/', 'annotation');
        }

        /*/ load routes from configuration file /*/
        $loadRoutesIfExists = function ($file, $alternativeFile, $directory) use (&$routes) {
            if (is_readable($directory.$file)) {
                $routes->import($directory.$file);
            } elseif (is_readable($directory.$alternativeFile)) {
                $routes->import($directory.$alternativeFile);
            }
        };
        $routingFiles = ['/routing.yml' => '/routing'.$environment.'.yml'];
        array_walk($routingFiles, $loadRoutesIfExists, $this->getConfigDir());

    }

    private function loadedConfigurations($containerBuilder, $extension)
    {
        $configuration = $containerBuilder->getExtensionConfig($extension);

        /* Flatten Array */
        return iterator_to_array(
            new \RecursiveIteratorIterator(
                new \RecursiveArrayIterator($configuration)
            )
        );
    }
}

/*EOF*/