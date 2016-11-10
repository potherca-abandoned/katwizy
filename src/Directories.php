<?php

namespace Potherca\Katwizy;

use Directory;

class Directories
{
    /** @var Directory */
    private $configDir;
    /** @var Directory */
    private $defaultConfigDir;
    /** @var Directory */
    private $projectDir;
    /** @var Directory */
    private $varDir;

    final public function __construct(
        Directory $configDir,
        Directory $defaultConfigDir,
        Directory $projectDir,
        Directory $varDir
    ) {
        $this->configDir = $configDir;
        $this->defaultConfigDir = $defaultConfigDir;
        $this->projectDir = $projectDir;
        $this->varDir = $varDir;
    }

    /** @return string */
    final public function getConfigDir()
    {
        return $this->configDir->path;
    }

    /** @return string */
    final public function getDefaultConfigDir()
    {
        return $this->defaultConfigDir->path;
    }

    /** @return string */
    final public function getProjectDir()
    {
        return $this->projectDir->path;
    }

    /** @return string */
    final public function getVarDir()
    {
        return $this->varDir->path;
    }
}
