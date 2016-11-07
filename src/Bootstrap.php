<?php

namespace Potherca\Katwizy;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Bootstrap
{
    /** @var AppKernel */
    private $kernel;
    /** @var ClassLoader */
    private $loader;

    final public function getKernel()
    {
        if ($this->kernel === null) {
            $rootDirectory = $this->getRootDirectory();

            $this->kernel = new AppKernel($rootDirectory, [
                AppKernel::ENVIRONMENT => AppKernel::DEVELOPMENT,// @TODO: Grab config from the environment
                AppKernel::DEBUG => true // @TODO: Match debugtoken from cookie/header/url to config:debugtoken
            ]);
        }

        return $this->kernel;
    }

    private function getLoader()
    {
        return $this->loader;
    }

    private function getRootDirectory()
    {
        $loader = $this->loader;
        $reflector = new \ReflectionObject($loader);
        // Autoloader at /vendor/composer/ClassLoader.php
        $rootDirectory = dirname(dirname(dirname($reflector->getFileName())));

        return dir($rootDirectory);
    }

    final public static function run(
        ClassLoader $loader,
        Request $request,
        AppKernel $kernel = null
    ) {
        $bootstrap = new static($loader, $kernel);

        $bootstrap->load();
        $response = $bootstrap->handle($request);
        $bootstrap->send($response);
        $bootstrap->terminate($request, $response);
    }

    /**
     * @param ClassLoader $loader
     * @param Directory $rootDirectory
     */
    final public function __construct(ClassLoader $loader, AppKernel $kernel = null)
    {
        $this->loader = $loader;
        $this->kernel = $kernel;
    }

    /*/ Autoload annotations /*/
    final public function load()
    {
        $projectPath = $this->getKernel()->getProjectDir();

        if (is_readable($projectPath.'/.env')) {
            $environmentVariables = new Dotenv($projectPath, '.env');
            $environmentVariables->load();
        }

        return AnnotationRegistry::registerLoader(array($this->getLoader(), 'loadClass'));
    }

    /*/ Run Framework /*/
    final public function handle(Request $request)
    {
        return $this->getKernel()->handle($request);
    }

    /*/ Run Framework /*/
    final public function send(Response $response)
    {
        return $response->send();
    }

    /*/ Run Framework /*/
    final public function terminate(Request $request, Response $response)
    {
        return $this->getKernel()->terminate($request, $response);
    }
}

/*EOF*/
