<?php

namespace Potherca\Katwizy;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Dotenv\Dotenv;
use Potherca\Katwizy\Factory\AppKernelFactory;
use Potherca\Katwizy\Factory\ConfigLoaderFactory;
use Potherca\Katwizy\Immutable\Debug;
use Potherca\Katwizy\Immutable\Environment;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\Request;

class Bootstrap
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var bool */
    private $debug;
    /** @var string */
    private $environment;
    /** @var ArgvInput|Request */
    private $input;
    /** @var ClassLoader */
    private $loader;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return bool
     */
    private function getDebug()
    {
        if ($this->debug === null) {
            $input = $this->input;

            switch (get_class($input)) {

                case ArgvInput::class:
                    $debug = ((int) getenv('SYMFONY_DEBUG') !== '0') && ($input->hasParameterOption(['--no-debug', '']) === false);
                break;


                case Request::class:
                    $debug = (bool) ((string) new Debug($input));
                break;


                default:
                    $debug = false;
                break;
            }

            $this->debug = $debug;
        }

        return $this->debug;
    }

    /**
     * @return string
     */
    private function getEnvironment()
    {
        if ($this->environment === null) {
            $input = $this->input;

            switch (get_class($input)) {

                case ArgvInput::class:
                    $environment = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: Environment::DEVELOPMENT);
                break;


                case Request::class:
                    $environment = (string) new Environment();
                break;


                default:
                    $environment = Environment::PRODUCTION;
                break;
            }

            $this->environment = $environment;
        }

        return $this->environment;
    }

    /** @return ClassLoader */
    private function getLoader()
    {
        return $this->loader;
    }

    /** @return string */
    private function getRootDirectory()
    {
        static $rootDirectory;

        if ($rootDirectory === null) {
            $loader = $this->getLoader();

            $reflector = new \ReflectionObject($loader);

            /* Autoloader at /vendor/composer/ClassLoader.php */
            $rootDirectory = dirname(dirname(dirname($reflector->getFileName())));
        }

        return $rootDirectory;
    }
    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public static function run(ClassLoader $loader, $input) {

        $bootstrap = new static($loader, $input);

        $bootstrap->load();

        $input = $bootstrap->input;
        $kernel = $bootstrap->createKernel();

        switch (get_class($input)) {

            case ArgvInput::class:
                $bootstrap->handleInput($kernel, $input);
                break;


            case Request::class:
                $bootstrap->handleRequest($kernel, $input);
                break;


            default:
                break;
        }
    }

    /**
     * @param ClassLoader $loader
     * @param ArgvInput|Request $input
     *
     * @throws \InvalidArgumentException
     */
    final public function __construct(ClassLoader $loader, $input)
    {
        $this->loader = $loader;
        if ($input instanceof ArgvInput || $input instanceof Request) {
            $this->input = $input;
        } else {
            throw $this->exception($input);
        }

    }

    /**
     * Loads pre-run requisites
     *
     * @throws \InvalidArgumentException
     */
    private function load()
    {
        $this->loadEnvFile();
        $this->registerAnnotationLoader();
        $this->loadDebug();
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return AppKernel
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     */
    private function createKernel()
    {
        $debug = $this->getDebug();
        $environment = $this->getEnvironment();
        $rootDirectory = $this->getRootDirectory();

        $configLoaderFactory = new ConfigLoaderFactory($rootDirectory, $environment);
        $configLoader = $configLoaderFactory->create();

        $kernelFactory = new AppKernelFactory($configLoader, $environment, $debug);

        return $kernelFactory->create();
    }

    /**
     * @param mixed $input
     *
     * @return \InvalidArgumentException
     */
    private function exception($input)
    {
        $type = gettype($input);

        if ($type === ' object') {
            $type = get_class($input);
        }

        $exception = new \InvalidArgumentException(
            'Given input must be one of "%s", "%s" given',
            implode('", "', [ArgvInput::class, Request::class]),
            $type
        );

        return $exception;
    }

    private function handleRequest(AppKernel $kernel, Request $input)
    {
        $response = $kernel->handle($input);
        $response = $response->send();
        $kernel->terminate($input, $response);
    }

    private function handleInput(AppKernel $kernel, ArgvInput $input)
    {
        $application = new Application($kernel);

        return $application->run($input);
    }

    private function loadEnvFile()
    {
        $path = $this->getRootDirectory();

        if (is_readable($path . '/.env')) {
            $environmentVariables = new Dotenv($path, '.env');
            $environmentVariables->load();
        }
    }

    private function registerAnnotationLoader()
    {
        AnnotationRegistry::registerLoader([$this->getLoader(), 'loadClass']);
    }

    private function loadDebug()
    {
        if ($this->getDebug()) {
            \Symfony\Component\Debug\Debug::enable();
        }
    }

}

/*EOF*/
