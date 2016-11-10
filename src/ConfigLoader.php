<?php

namespace Potherca\Katwizy;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var Directories */
    private $directories;
    /** @var string */
    private $environment;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @return string */
    final public function getConfigDir()
    {
        return $this->directories->getConfigDir();
    }

    /** @return string */
    final public function getDefaultConfigDirectory()
    {
        return $this->directories->getDefaultConfigDir();
    }

    /** @return string */
    private function getEnvironment()
    {
        return $this->environment;
    }

    /** @return string */
    public function getProjectDir()
    {
        return $this->directories->getProjectDir();
    }

    /** @return string */
    public function getVarDir()
    {
        return $this->directories->getVarDir();
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function __construct(Directories $directories, $environment) {
        $this->directories = $directories;
        $this->environment = $environment;
    }

    /**
     * Load bundles from project configuration
     *
     * @return array|mixed
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    final public function loadBundles()
    {
        $projectBundleConfig = [];
        if (is_readable($this->getConfigDir() . '/bundles.yml')) {
            $projectBundleConfig = Yaml::parse(
                file_get_contents($this->getConfigDir() . '/bundles.yml')
            );

            return $projectBundleConfig;
        }

        return $projectBundleConfig;
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @param array $defaultConfig
     *
     * @throws \LogicException
     */
    final public function loadDefaultConfiguration(ContainerBuilder $containerBuilder, array $defaultConfig)
    {
        $containerBuilder->loadFromExtension('framework', $defaultConfig);
    }

    /**
     * @param LoaderInterface $loader
     *
     * @throws \Exception
     */
    final public function loadDefaultSecurity(LoaderInterface $loader)
    {
        /* Load default security configuration file */
        if (file_exists($this->getConfigDir() . '/security.yml') === false
            && file_exists($this->getConfigDir() . '/security_' . $this->getEnvironment() . '.yml') === false
        ) {
            $loader->load($this->getDefaultConfigDirectory() . '/security.yml');
        }
    }

    /**
     * @param LoaderInterface $loader
     */
    final public function loadProjectConfig(LoaderInterface $loader)
    {
        /* Load project configuration files */
        $projectConfigFiles = [
            '/config.yml' => '/config_' . $this->getEnvironment() . '.yml',
            '/security.yml' => '/security_' . $this->getEnvironment() . '.yml',
            '/services.yml' => '/services_' . $this->getEnvironment() . '.yml',
        ];
        array_walk($projectConfigFiles, $this->loadConfigIfExists($loader), $this->getConfigDir());
    }

    /**
     * @param LoaderInterface $loader
     */
    final public function loadProjectParameters(LoaderInterface $loader)
    {
        $parametersFile = [
            '/parameters.yml' => '/parameters_'.$this->getEnvironment().'.yml'
        ];

        array_walk($parametersFile, $this->loadConfigIfExists($loader), $this->getConfigDir());
    }

    /**
     * @param ContainerBuilder $containerBuilder
     *
     * @throws \LogicException
     */
    final public function loadRequiredConfig(ContainerBuilder $containerBuilder)
    {
        /*/ Make sure required configuration is set /*/
        $configuration = $this->loadedConfigurations($containerBuilder, 'framework');
        if (array_key_exists('secret', $configuration) === false) {
            $containerBuilder->loadFromExtension('framework', ['secret' => 'S0ME_SECR3T']);
        }
    }

    /**
     * @param RouteCollectionBuilder $router
     */
    final public function loadRoutes(RouteCollectionBuilder $router)
    {
        $routingFiles = ['/routing.yml' => '/routing' . $this->getEnvironment() . '.yml'];

        array_walk($routingFiles, $this->loadRouteIfExists($router), $this->getConfigDir());
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    private function loadedConfigurations(ContainerBuilder $containerBuilder, $extension)
    {
        $configuration = $containerBuilder->getExtensionConfig($extension);

        /* Flatten Array */
        return iterator_to_array(
            new \RecursiveIteratorIterator(
                new \RecursiveArrayIterator($configuration)
            )
        );
    }

    private function loadConfigIfExists(LoaderInterface $loader)
    {
        return function ($file, $alternativeFile, $directory) use (&$loader) {
            if (is_readable($directory.$file)) {
                $loader->load($directory.$file);
            } elseif (is_numeric($alternativeFile) === false
                && is_readable($directory.$alternativeFile)
            ) {
                $loader->load($directory.$alternativeFile);
            }
        };
    }

    private function loadRouteIfExists(RouteCollectionBuilder $routes)
    {
        return function ($file, $alternativeFile, $directory) use (&$routes) {
            if (is_readable($directory.$file)) {
                $routes->import($directory.$file);
            } elseif (is_readable($directory.$alternativeFile)) {
                $routes->import($directory.$alternativeFile);
            }
        };
    }
}

/*EOF*/
