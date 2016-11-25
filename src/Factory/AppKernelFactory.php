<?php

namespace Potherca\Katwizy\Factory;

use Potherca\Katwizy\AppKernel;
use Potherca\Katwizy\ConfigLoader;
use Potherca\Katwizy\Immutable\Debug;
use Potherca\Katwizy\Immutable\Environment;

class AppKernelFactory
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var ConfigLoader */
    private $configLoader;
    /** @var Debug */
    private $debug;
    /** @var Environment */
    private $environment;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param ConfigLoader $configLoader
     * @param string $environment
     * @param bool $debug
     */
    final public function __construct(ConfigLoader $configLoader, $environment, $debug)
    {
        $this->debug = $debug;
        $this->configLoader = $configLoader;
        $this->environment = $environment;
    }

    /**
     * @return AppKernel
     * @throws \UnexpectedValueException
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    final public function create()
    {
        return new AppKernel(
            $this->configLoader,
            $this->environment,
            $this->debug
        );
    }
}

/*EOF*/
