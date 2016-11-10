<?php

namespace Potherca\Katwizy;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Bootstrap
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var AppKernel */
    private $kernel;
    /** @var ClassLoader */
    private $loader;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return AppKernel
     *
     * @throws \RuntimeException
     */
    final public function getKernel()
    {
        if ($this->kernel === null) {

            $debug = true;  // @TODO: Match debug-token from cookie/header/url to config:debugtoken
            $environment = AppKernel::DEVELOPMENT;// @TODO: Grab config from the environment

            $rootDirectory = $this->getRootDirectory();
            $varDir = $rootDirectory . '/var/' . $environment;
            $configDir = $rootDirectory . '/config';

            if (is_dir($configDir) === false) {
                $configDir = $rootDirectory;
            }

            $this->ensureDirectoryExists($varDir);

            $directories = new Directories(
                dir($configDir),
                dir($rootDirectory.'/vendor/symfony/framework-standard-edition/app/config'),
                dir($rootDirectory),
                dir($varDir)
            );

            $configLoader = new ConfigLoader($directories, $environment);

            $this->kernel = new AppKernel(
                $configLoader,
                $environment,
                $debug
            );
        }

        return $this->kernel;
    }

    /** @return ClassLoader */
    private function getLoader()
    {
        return $this->loader;
    }

    /** @return string */
    private function getRootDirectory()
    {
        $loader = $this->loader;
        $reflector = new \ReflectionObject($loader);

        /* Autoloader at /vendor/composer/ClassLoader.php */
        return dirname(dirname(dirname($reflector->getFileName())));
    }

    /**
     * @param ClassLoader $loader
     * @param Request $request
     * @param AppKernel|null $kernel
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
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

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param ClassLoader $loader
     * @param AppKernel|null $kernel
     */
    final public function __construct(ClassLoader $loader, AppKernel $kernel = null)
    {
        $this->loader = $loader;
        $this->kernel = $kernel;
    }

    /**
     * Loads pre-run requisites
     *
     * @throws \InvalidArgumentException
     */
    final public function load()
    {
        $projectPath = $this->getKernel()->getProjectDir();

        if (is_readable($projectPath.'/.env')) {
            $environmentVariables = new Dotenv($projectPath, '.env');
            $environmentVariables->load();
        }

        AnnotationRegistry::registerLoader(array($this->getLoader(), 'loadClass'));
    }

    /**
     * Proxy for AppKernel::handle()
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    final public function handle(Request $request)
    {
        return $this->getKernel()->handle($request);
    }

    /**
     * Send given response
     *
     * @param Response $response
     *
     * @return Response
     */
    final public function send(Response $response)
    {
        return $response->send();
    }

    /**
     * Proxy for AppKernel::terminate()
     *
     * @param Request $request
     * @param Response $response
     */
    final public function terminate(Request $request, Response $response)
    {
        return $this->getKernel()->terminate($request, $response);
    }

    /**
     * @param string $varDir
     *
     * @throws \RuntimeException
     */
    private function ensureDirectoryExists($varDir)
    {
        if (is_dir($varDir) === false) {
            if (file_exists($varDir) === true) {
                throw new \RuntimeException(
                    sprintf(
                        'Could not create directory "%s", file already exists at given location.',
                        $varDir
                    )
                );
            } else {
                /* @NOTE: Error is suppressed with "@" so an exception can be thrown instead of triggering an error */
                /** @noinspection NotOptimalIfConditionsInspection *///No use in checking dir before creation
                if (@mkdir($varDir, 0777, true) === false && is_dir($varDir) === false) {
                    throw new \RuntimeException(sprintf('Could not create directory "%s"', $varDir));
                }
            }
        } elseif (is_writable($varDir) === false) {
            throw new \RuntimeException(sprintf('Could not write in directory "%s"', $varDir));
        }
    }
}

/*EOF*/
