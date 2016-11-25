<?php

namespace Potherca\Katwizy\Immutable;

class Environment
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const DEBUG = 'debug';
    const DEVELOPMENT = 'dev';
    const PRODUCTION = 'prod';

    private $environment;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    private function getEnvironment()
    {
        if ($this->environment === null) {
            $this->environment = $this->createEnvironment();
        }

        return $this->environment;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function __toString()
    {
        return $this->getEnvironment();
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    private function createEnvironment()
    {
        $environment = Environment::PRODUCTION;

        $symfonyEnvironment = getenv('SYMFONY_ENV');

        if ($symfonyEnvironment !== false) {
            $environment = $symfonyEnvironment;
        }

        return $environment;
    }
}

/*EOF*/
