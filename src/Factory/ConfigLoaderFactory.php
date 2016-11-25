<?php

namespace Potherca\Katwizy\Factory;

use Potherca\Katwizy\ConfigLoader;
use Potherca\Katwizy\Directories;

class ConfigLoaderFactory
{
    /** @var string */
    private $environment;
    /** @var string */
    private $rootDirectory;

    /**
     * @param string $rootDirectory
     * @param string $environment
     *
     * @throws \RuntimeException
     */
    final public function __construct($rootDirectory, $environment)
    {
        $this->rootDirectory = (string) $rootDirectory;
        $this->environment = (string) $environment;
    }

    /**
     * @return ConfigLoader
     * @throws \RuntimeException
     */
    final public function create()
    {
        $rootDirectory = $this->rootDirectory;
        $environment = $this->environment;

        $varDir = $rootDirectory . '/var/' . $environment;
        $configDir = $rootDirectory . '/config';

        if (is_dir($configDir) === false) {
            $configDir = $rootDirectory;
        }

        $this->ensureDirectoryExists($varDir);

        $directories = new Directories(
            dir($configDir),
            dir($rootDirectory . '/vendor/symfony/framework-standard-edition/app/config'),
            dir($rootDirectory),
            dir($varDir)
        );

        return new ConfigLoader($directories, $environment);
    }

    /**
     * @param string $varDir
     *
     * @throws \RuntimeException
     */
    private  function ensureDirectoryExists($varDir)
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
